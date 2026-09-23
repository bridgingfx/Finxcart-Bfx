<!DOCTYPE html>
<html>
<head>
    <title>{{ $contract_title }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* Base Styles for PDF - Dompdf prefers simple, direct CSS */
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 50px;
        }
        h3 {
            font-size: 16pt;
            margin-top: 25px;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
        }
        h4 {
            font-size: 14pt;
            margin-top: 20px;
        }
        p, ul {
            line-height: 1.6;
            margin-bottom: 10px;
            text-align: justify;
        }
        ul {
            list-style-type: none;
            padding-left: 0;
        }
        ul li {
            margin-bottom: 5px;
            padding-left: 20px;
            position: relative;
        }
        ul li:before {
            content: "•"; /* Custom bullet point */
            color: #333;
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
            position: absolute;
            left: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            position: relative; /* Essential for positioning the logo absolutely */
        }
        .header h1 {
            font-size: 20pt;
            color: #1a73e8; /* Finxcart brand color */
            margin: 0;
        }
        /* New Logo Style for Top Right Positioning */
        .logo {
            position: absolute;
            top: 0; /* Adjust vertical position */
            right: 0; /* Adjust horizontal position */
            width: 100px; /* Adjust size as needed */
            height: auto;
        }

        /* Signature Block */
        .signature-block {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .signature-row {
            margin-bottom: 25px;
        }
        .signature-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .signature-line {
            border-bottom: 1px dashed #666;
            display: inline-block;
            min-width: 250px;
            height: 20px;
            vertical-align: bottom;
        }
        .signature-image {
            /* Increased max-width for better visibility */
            max-width: 250px;
            max-height: 90px; /* Adjusted max-height proportionally or as needed */
            margin-top: 10px;
            border-bottom: 1px dashed #666;
            padding-bottom: 5px;
        }

        /* Footer/Pagination */
        .footer {
            position: fixed;
            bottom: 30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        /* Dompdf specific: embedding images */
        .e-signature-space {
            margin-top: 20px;
            margin-bottom: 30px;
        }

        /* Citation Styles (to hide citations in final PDF look) */
        , [cite] {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="storage/company/2025-05-21-682ddd7d308e6.webp" alt="Company Logo" class="logo">
            <p style="text-transform: uppercase; font-size: 24pt; font-weight: bold; margin-bottom: 5px; text-align: center;">Finxcart Seller E-contract</p>
            <h2>{{ $contract_title }}</h2>
        </div>

        {!! $contract_html_content !!}

        <h4>Electronic Signature and Agreement</h4>
        <p>By providing your electronic signature below and clicking "I Agree," you acknowledge that you have read, understood, and agree to be bound by this Agreement. This electronic signature has the same legal effect as a handwritten signature under the Electronic Signatures in Global and National Commerce Act (E-SIGN) and similar laws.</p>

        <div class="signature-block">
            <div class="e-signature-space">
                <p style="font-weight: bold; margin-bottom: 5px;">[E-Signature]</p>
                <?php
                    // IMPORTANT: Dompdf requires local images to be converted to Base64 data URIs.
                    // This logic assumes file_get_contents is available and the path is accessible.
                    // Note: This logic block may need adjustment depending on your Laravel/PHP environment
                    // to ensure $signature_image_path is valid and file_get_contents works in a Dompdf context.
                    // If you're embedding Dompdf correctly, you might need to use a data URI here.
                    // $type = mime_content_type($signature_image_path);
                    // $imageData = base64_encode(file_get_contents($signature_image_path));
                    // $src = 'data:' . $type . ';base64,' . $imageData;
                    // dd($src);
                ?>
                <img src="{{ $signature_image_path}}" alt="Seller Signature" width="250px" class="signature-image">
            </div>

            <div class="signature-row">
                <span class="signature-label">Seller Full Name:</span>
                <span class="signature-line" style="min-width: 350px; padding-left: 10px;">{{ $seller_full_name }}</span>
            </div>

            <div class="signature-row">
                <span class="signature-label">Date:</span>
                <span class="signature-line" style="min-width: 350px; padding-left: 10px;">{{ \Carbon\Carbon::parse($agreement_date)->format('F d, Y') }}</span>
            </div>

            <div class="signature-row">
                <span class="signature-label">Seller Entity (if applicable):</span>
                <span class="signature-line" style="min-width: 350px; padding-left: 10px;">{{ $seller_entity ?? 'N/A' }}</span>
            </div>

            <p style="font-style: italic; margin-top: 30px;">[I Agree Button: Click to Proceed and Confirm Agreement (Omitted from PDF)]</p>
        </div>

    </div>

    <div class="footer">
        www.finxcart.com
    </div>
</body>
</html>
