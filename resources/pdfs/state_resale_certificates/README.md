# State Resale Certificate PDF Templates

This directory contains the PDF templates for each state's resale certificate form.

## File Naming Convention

PDF files should be named using lowercase with underscores for spaces:
- `new_york.pdf` - New York
- `district_of_columbia.pdf` - District of Columbia
- `california.pdf` - California
- `texas.pdf` - Texas
- etc.

## Template Requirements

1. PDF files should be the official blank forms from each state
2. Forms should be fillable or have clear areas where data can be overlaid
3. Keep file sizes reasonable (under 5MB per template)

## Adding Templates for PDF Field Mapper

The PDF Field Mapper tool (available at `/dev/cert-mapper`) automatically detects any PDFs in this directory.

1. Download the official resale certificate form from the state's tax authority website
2. Name it using lowercase and underscores (e.g., `north_carolina.pdf`)
3. Place it in this directory
4. The PDF will automatically appear in the dropdown on the field mapper tool
5. No code changes or database entries required!

## Using the Field Mapper

1. Navigate to `/dev/cert-mapper` (local environment only)
2. Your new PDF will appear in the dropdown
3. Select it and drag fields onto the PDF
4. Copy the generated `fillFormFields` function
5. Create a new certificate class in `app/Services/StateCertificates/States/`

## Missing Templates

If a template is not found for a state, the system will generate a generic certificate with all required information.
