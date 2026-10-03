# State Resale Certificate PDF Templates

This directory holds the official blank resale certificate form for each state. Certificates are made by importing the form with FPDI and writing the buyer's details at fixed millimetre coordinates.

- `config/resale_cert.php` maps each state to its certificate class and template file (Georgia and New Jersey also have a `template_out_of_state` form).
- The certificate classes live in `app/Domains/ResaleCert/Pdf/States/`. Each class's `fillFormFields()` writes fields with `$this->writeAt($pdf, x, y, $data->field)` and stamps the signature with `addSignatureWithHeight()`.

## File Naming Convention

Name files in lowercase with underscores for spaces, for example `new_york.pdf` or `south_carolina.pdf`.

## Mapping Fields

The PDF coordinate mapper is at `/admin/tools/pdf-mapper` (admin role). It reads the `writeAt()` calls from each class, so keep that call shape: numeric coordinates and a `$data->field` or a literal string. Its sample preview renders a certificate with fixed sample data and a red 5 mm grid, in-state or out-of-state, so you can check every field against the form.

## Updating a Template

1. Download the current form from the state's tax agency and note its revision mark.
2. Flatten it before copying it here. Most downloaded state forms use object streams or compression that the free FPDI parser cannot read ("compression technique not supported"). Saving the PDF with pdf-lib and `useObjectStreams: false`, after `form.flatten()` for fillable forms, gives a file FPDI imports. Remove buttons and help banners (for example "Print" and "Reset") before flattening, or they are baked into the page.
3. Replace the file here under the same name.
4. Render the sample certificate in-state and out-of-state with the grid, and fix the coordinates in the state's class until every field sits on its line or in its box.
5. Run `tests/Feature/ResaleCert/CertificateRenderingTest.php`. It renders every state and checks the page count matches the template.
