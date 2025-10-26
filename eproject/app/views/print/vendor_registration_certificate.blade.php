<html>
    <head>
        <style type='text/css'>
            body, html {
                margin: 0;
                padding: 0;
            }
            body {
                color: black;
                display: table;
                font-family: Arial, serif;
                font-size: 24px;
                text-align: center;
            }
            .container {
                position: fixed;
                top: 0;
                left: 0;
                border: 20px solid #E6B0AA;
                width: 100%;
                height: 100%;
                display: table-cell;
                vertical-align: middle;
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            .organization {
                font-size: 32px;
                /*font-style: italic;*/
                margin: 20px auto;
            }

            .marquee-small {
                color: #922B21;
                font-size: 32px;
            }
            .marquee-large {
                color: #922B21;
                font-size: 72px;
            }
            .person {
                border-bottom: 2px solid black;
                font-size: 32px;
                /*font-style: italic;*/
                margin: 20px auto;
                width: 600px;
            }
            .vendor-code {
                font-size: 32px;
                margin: 30px auto;
                width: 600px;
            }
            .date-description {
                font-size: 20px;
            }
            .date {
                color: #922B21;
            }
            .header-spacing {
                padding-top:50px;
            }
            .logo-spacing {
                padding-top:50px;
            }
            img.logo {
                width:200px;
                height:200px;
            }
            .roc-number {
                white-space: nowrap;
            }
            .footer {
                position: fixed;
                left: 0;
                bottom: 0;
                width: 100%;
                text-align: center;
                padding: 30px;
            }
            .committee {
                padding:10px 0;
                font-weight: bold;
            }
            .footer-company {
                padding:10px 0;
                font-size: 16px;
            }
            .disclaimer {
                padding:10px 0;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header-spacing"></div>
            <div>
                <img src="{{ $companyLogoPath }}" class="logo"/>
            </div>
            <div class="logo-spacing"></div>

            <div class="marquee-small">
                Certificate of 
            </div>

            <div class="marquee-large">
                Registration
            </div>

            <div class="marquee-small">
                to
            </div>

            <div class="person">
                {{{ $vendor->name }}} <span class="roc-number">({{{ $vendor->reference_no }}})</span>
            </div>

            <div class="vendor-code">
                {{ $vendor->getVendorCode() }}
            </div>

            <div class="marquee-small">
                for
            </div>

            <div class="organization">
                being a {{{ $companyProfile->name }}}
            </div>

            <div class="organization">
                Approved Vendor
            </div>

            <div class="date-description">
                from <span class="date">{{ \Carbon\Carbon::parse($vendor->activation_date)->format(\Config::get('dates.submitted_at')) }}</span>
            </div>
            <div class="date-description">
                to <span class="date">{{ \Carbon\Carbon::parse($vendor->expiry_date)->format(\Config::get('dates.submitted_at')) }}</span>
            </div>

            <div class="footer">
                <div class="footer-company">
                    {{{ $companyProfile->name }}}
                </div>
                <div class="disclaimer">
                    (This certificate is computer generated and no signature is required)
                </div>
            </div>
        </div>
    </body>
</html>