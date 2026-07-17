{{--
    State-mandated statements appended to the generic waiver bodies (registry
    'extra_clauses'), for states with a content mandate but no statutory form:
    CO's C.R.S. § 38-22-119(2) third-party-debts statement. Null-guarded so
    render snapshots frozen before this key existed still render unchanged.
--}}
@foreach ($waiver['form']['extra_clauses'] ?? [] as $clause)
    <p>{{ $clause }}</p>
@endforeach
