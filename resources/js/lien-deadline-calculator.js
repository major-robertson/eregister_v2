/*
 * Free mechanics lien deadline calculator (EREG-13). Runs over the per-state
 * rule JSON inlined in the page (App\Domains\Lien\Seo\DeadlineRulesExport).
 *
 * evaluate() and the date math below are a line-by-line port of
 * DeadlineRulesExport::evaluate() and App\Domains\Lien\Engine\RuleDateMath,
 * which tests/Feature/Lien/DeadlineCalculatorContractTest.php checks against
 * the paid product's DeadlineCalculator for every state. Change them together.
 */

const DAY = 86400000;

/* ------------------------------------------------------------ date math */

// Dates are UTC-midnight timestamps, so no time zone or DST change can move a day.
export function parseDate(value) {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value || '');
    if (!match) {
        return null;
    }
    const [y, m, d] = [Number(match[1]), Number(match[2]), Number(match[3])];
    if (y < 1900 || y > 2199) {
        return null;
    }
    const t = Date.UTC(y, m - 1, d);

    // Reject 2026-02-30 and the like rather than rolling them over.
    return new Date(t).getUTCDate() === d ? t : null;
}

export function toIso(t) {
    return new Date(t).toISOString().slice(0, 10);
}

function addDays(t, days) {
    return t + days * DAY;
}

function daysInMonth(year, month) {
    return new Date(Date.UTC(year, month + 1, 0)).getUTCDate();
}

// Carbon's addMonthsNoOverflow(): same day in the target month, clamped to its
// last day (Jan 31 + 1 month = Feb 28/29, never Mar 3).
function addMonthsNoOverflow(t, months) {
    const d = new Date(t);
    const target = new Date(Date.UTC(d.getUTCFullYear(), d.getUTCMonth() + months, 1));
    const y = target.getUTCFullYear();
    const m = target.getUTCMonth();

    return Date.UTC(y, m, Math.min(d.getUTCDate(), daysInMonth(y, m)));
}

/** RuleDateMath::apply(). */
export function applyMethod(anchor, method, offsetDays, offsetMonths, dayOfMonth) {
    const d = new Date(anchor);
    const y = d.getUTCFullYear();
    const m = d.getUTCMonth();

    switch (method || 'days_after_date') {
        case 'days_after_date':
            return addDays(anchor, offsetDays || 0);
        case 'months_after_date':
            return addMonthsNoOverflow(anchor, offsetMonths || 0);
        case 'month_day_after_month_of_date': {
            // Start of the anchor month, N months on, then Carbon's setDay()
            // (which overflows into the next month like Date.UTC does).
            const first = new Date(Date.UTC(y, m + (offsetMonths || 0), 1));

            return Date.UTC(first.getUTCFullYear(), first.getUTCMonth(), dayOfMonth || 1);
        }
        case 'days_after_end_of_month_of_date':
            return addDays(Date.UTC(y, m + 1, 0), offsetDays || 0);
        case 'days_before_date':
            return addDays(anchor, -Math.max(0, offsetDays || 0));
        default:
            throw new Error(`Unknown calc method ${method}`);
    }
}

/** RuleDateMath::resolveAnchor() over an export rule (anchor already resolved by the export). */
function lookup(dates, key) {
    let value = dates[key] ?? null;
    // The engine has no contract date on a project; it reads the first furnishing date.
    if (value === null && key === 'contract') {
        value = dates.first_furnish ?? null;
    }

    return value;
}

function resolveAnchor(rule, dates) {
    if (!rule.anchor) {
        return lookup(dates, rule.trigger_event);
    }

    const found = (rule.dates || []).map((key) => lookup(dates, key)).filter((t) => t !== null);
    if (!found.length) {
        return null;
    }

    return rule.anchor === 'later_of' ? Math.max(...found) : Math.min(...found);
}

/** RuleDateMath::dueDate() for the rules the export carries (never days_before_date). */
function dueDate(rule, dates) {
    const anchor = resolveAnchor(rule, dates);
    if (anchor === null) {
        return { date: null, anchor: null, missing: [rule.trigger_event] };
    }

    return {
        date: applyMethod(anchor, rule.calc_method, rule.offset_days, rule.offset_months, rule.day_of_month),
        anchor,
        missing: [],
    };
}

function nocLogic(state, noc, base, prelimBeforeNoc) {
    if (noc === null) {
        return { blocked: false, shortened: false, deadline: base };
    }
    if (state.noc_requires_prior_prelim && !prelimBeforeNoc) {
        return { blocked: true, shortened: false, deadline: null };
    }
    const days = state.lien_after_noc_days;
    if (state.noc_shortens_deadline && days) {
        const nocDeadline = addDays(noc, days);
        if (nocDeadline < base) {
            return { blocked: false, shortened: true, deadline: nocDeadline };
        }
    }

    return { blocked: false, shortened: false, deadline: base };
}

export function matchingRules(state, claimant, scope) {
    return state.rules.filter((r) => (r.claimant === claimant || r.claimant === 'any') && (r.scope === scope || r.scope === 'both'));
}

/**
 * DeadlineRulesExport::evaluate(). `dates` maps short keys to Y-m-d strings.
 * Returns one row per matching rule: status date | display | missing | no_rights | blocked.
 */
export function evaluate(state, claimant, scope, dates, prelimBeforeNoc = false) {
    const rules = matchingRules(state, claimant, scope);
    const parsed = {};
    for (const [key, value] of Object.entries(dates)) {
        parsed[key] = parseDate(value);
    }
    const noc = parsed.noc ?? null;

    // The notice of intent counts back from the lien deadline: the first lien
    // rule, shortened by a notice of completion but never blocked.
    const lienRule = rules.find((r) => r.doc === 'mechanics_lien');
    let lienForNoi = null;
    if (lienRule) {
        lienForNoi = dueDate(lienRule, parsed);
        if (lienForNoi.date !== null) {
            const result = nocLogic(state, noc, lienForNoi.date, prelimBeforeNoc);
            lienForNoi = { ...lienForNoi, date: result.shortened ? result.deadline : lienForNoi.date };
        }
    }

    return rules.map((rule) => {
        const row = { doc: rule.doc, claimant: rule.claimant, scope: rule.scope, required: rule.is_required, status: 'date', date: null, missing: [], rule };

        if ((rule.doc === 'mechanics_lien' || rule.doc === 'lien_enforcement') && state.lien_rights[claimant] === false) {
            return { ...row, status: 'no_rights' };
        }

        if (rule.doc === 'noi') {
            if (!lienForNoi || lienForNoi.date === null) {
                return { ...row, status: 'missing', missing: lienForNoi ? lienForNoi.missing : ['last_furnish'] };
            }
            const lead = Math.max(0, state.noi_lead_time_days || 0);

            return { ...row, date: toIso(applyMethod(lienForNoi.date, 'days_before_date', lead)), lead_time_days: lead, lien_date: toIso(lienForNoi.date) };
        }

        if (rule.display) {
            return { ...row, status: 'display', display: rule.display };
        }

        const due = dueDate(rule, parsed);
        if (due.date === null) {
            return { ...row, status: 'missing', missing: due.missing };
        }

        let date = due.date;
        if (rule.doc === 'mechanics_lien') {
            const result = nocLogic(state, noc, date, prelimBeforeNoc);
            if (result.blocked) {
                return { ...row, status: 'blocked' };
            }
            if (result.shortened) {
                row.noc_shortened = true;
                row.original_date = toIso(date);
                date = result.deadline;
            }
        }

        return { ...row, date: toIso(date), anchor: toIso(due.anchor) };
    });
}

/* ------------------------------------------------------------ the form */

const DOCUMENTS = [
    ['prelim_notice', 'Preliminary notice'],
    ['noi', 'Notice of intent to lien'],
    ['mechanics_lien', 'Mechanics lien'],
];

const ROLE_SHORT = {
    gc: 'General contractor',
    subcontractor: 'Subcontractor',
    sub_sub_contractor: 'Sub-subcontractor',
    supplier_to_owner: 'Supplier to the owner',
    supplier_to_contractor: 'Supplier to the general contractor',
    supplier_to_subcontractor: 'Supplier to a subcontractor',
};

// The dates the calculator asks for, in form order. Others a rule may name
// (contract termination, recorded lien) are never known on a project, so the
// engine treats them as blank too.
const INPUTS = {
    first_furnish: { label: 'First day you furnished labor or materials', optional: false },
    last_furnish: { label: 'Last day you furnished labor or materials', optional: false },
    completion: { label: 'Project completion date', optional: false },
    contract: { label: 'Contract date', optional: false },
    special_fab: { label: 'Delivery of specially fabricated materials', optional: true, help: 'Only if you made materials to order for this project.' },
    noc: { label: 'Notice of completion filed', optional: true, help: 'Leave blank if the owner has not filed one.' },
};

const NAMES = {
    first_furnish: 'first furnishing date',
    last_furnish: 'last furnishing date',
    completion: 'completion date',
    contract: 'contract date',
};

function formatDate(iso) {
    return new Date(parseDate(iso)).toLocaleDateString('en-US', { timeZone: 'UTC', weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' });
}

function sentence(text) {
    return text.charAt(0).toUpperCase() + text.slice(1);
}

function todayUtc() {
    const now = new Date();

    return Date.UTC(now.getFullYear(), now.getMonth(), now.getDate());
}

export default function lienDeadlineCalculator({ source, state = '' } = {}) {
    return {
        states: {},
        state,
        role: '',
        scope: 'residential',
        dates: { first_furnish: '', last_furnish: '', completion: '', contract: '', special_fab: '', noc: '' },
        prelimBeforeNoc: false,

        init() {
            try {
                this.states = JSON.parse(document.getElementById(source)?.textContent || '{}');
            } catch {
                this.states = {};
            }
        },

        get current() {
            return this.states[this.state] || null;
        },

        get stateList() {
            return Object.values(this.states).map((s) => ({ code: s.code, name: s.name }));
        },

        get ready() {
            return Boolean(this.current && this.role);
        },

        get hasLienRights() {
            return Boolean(this.current && this.current.lien_rights[this.role] !== false);
        },

        get asksNoc() {
            const s = this.current;

            return Boolean(s && this.hasLienRights && ((s.noc_shortens_deadline && s.lien_after_noc_days) || s.noc_requires_prior_prelim));
        },

        /** The dates the selected rules read, in form order. */
        get inputs() {
            if (!this.ready) {
                return [];
            }
            const rules = matchingRules(this.current, this.role, this.scope);
            const lien = rules.find((r) => r.doc === 'mechanics_lien');
            const needed = new Set();
            for (const rule of rules) {
                if (rule.doc === 'mechanics_lien' && !this.hasLienRights) {
                    continue;
                }
                const source = rule.doc === 'noi' ? lien : rule;
                (source?.inputs || []).forEach((key) => needed.add(key));
            }
            if (this.asksNoc) {
                needed.add('noc');
            }

            return Object.keys(INPUTS).filter((key) => needed.has(key)).map((key) => ({ key, ...INPUTS[key] }));
        },

        get rows() {
            if (!this.ready) {
                return [];
            }
            const s = this.current;
            const dates = {};
            for (const input of this.inputs) {
                dates[input.key] = this.dates[input.key] || null;
            }
            const results = evaluate(s, this.role, this.scope, dates, this.prelimBeforeNoc);
            const today = todayUtc();
            const who = ROLE_SHORT[this.role] + (this.scope === 'commercial' ? ', commercial project' : ', residential project');

            return DOCUMENTS.flatMap(([doc, label]) => {
                const matches = results.filter((r) => r.doc === doc);
                if (!matches.length) {
                    const inState = s.rules.some((r) => r.doc === doc);

                    return [{ key: doc, label, who, value: inState ? 'Not required for your role' : `Not required in ${s.name}`, how: '', muted: true, passed: false }];
                }

                return matches.map((r, i) => ({ key: `${doc}-${i}`, label, who, ...this.present(r, today) }));
            });
        },

        present(r, today) {
            const s = this.current;
            if (!r.required) {
                return { value: 'Not required for your role', how: '', muted: true, passed: false };
            }
            switch (r.status) {
                case 'no_rights':
                    return { value: `No lien rights for this role in ${s.name}`, how: '', muted: true, passed: false };
                case 'blocked':
                    return { value: 'Lien rights lost', how: 'The notice of completion was filed before your preliminary notice was sent.', muted: false, passed: false };
                case 'display':
                    return { value: sentence(r.display), how: '', muted: false, passed: false, text: true };
                case 'missing': {
                    const names = r.missing.map((key) => NAMES[key] || 'project dates');

                    return { value: `Enter the ${[...new Set(names)].join(' and ')} to see this date`, how: '', muted: true, passed: false };
                }
            }

            const passed = parseDate(r.date) < today;
            let how;
            if (r.doc === 'noi') {
                how = r.lead_time_days > 0
                    ? `${r.lead_time_days} days before the lien filing deadline (${formatDate(r.lien_date)}).`
                    : `Before the lien is filed, so no later than the lien filing deadline (${formatDate(r.lien_date)}).`;
            } else if (r.noc_shortened) {
                how = `${s.lien_after_noc_days} days after the notice of completion, which comes before the usual deadline of ${formatDate(r.original_date)}.`;
            } else {
                how = `${sentence(r.rule.when || 'see the statute')}. Counted from ${formatDate(r.anchor)}.`;
            }

            return { value: formatDate(r.date), how, muted: false, passed };
        },
    };
}
