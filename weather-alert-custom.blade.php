<!DOCTYPE html>
<html>
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>

body {
            margin: 0;
            padding: 0;
            width: 100%;
            background-color: #f1f3f6;
            font-family: 'Poppins', sans-serif;
        }

        .wrapper {
            width: 100%;
            margin: 0;
            padding: 20px;
            background-color: #f1f3f6;
            -premailer-cellpadding: 0;
            -premailer-cellspacing: 0;
            -premailer-width: 100%;
        }

        .content {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 0;
        }

        .main {
            width: 100%;
            margin: 0;
            padding: 0;
            border-radius: 3px;
            background-color: #ffffff;
            border: 1px solid #e8e5ef;
            box-shadow: 0 2px 0 rgba(0, 0, 150, 0.025);
        }


        h2 {
            font-size: 22px !important;
            color: rgb(61, 72, 82);
            font-family: 'Poppins', sans-serif;
            font-weight: 700 !important;
            padding-left: 25px !important;
            padding-right: 25px !important;
        }

        h2 a {
            background-color: rgb(45, 55, 72) !important;
            color: rgb(255, 255, 255) !important;
            padding: 10px 22px !important;
            border-radius: 8px !important;
            text-decoration: navajowhite !important;
            margin: 0 auto !important;
            margin-bottom: 0px !important;
            max-width: 130px !important;
            display: block !important;
            margin-bottom: 50px !important;
            font-family: 'Poppins', sans-serif !important;
            font-size: 16px !important;
            font-weight: 400 !important;
            text-align: center !important;
            padding-left: 25px !important;
            padding-right: 25px !important;
        }

        p {
            margin-top: 0px !important;
            margin-bottom: 8px !important;
            font-family: 'Poppins', sans-serif;
            font-size: 16px !important;
            color: rgb(113, 128, 150);
            padding-left: 25px !important;
            padding-right: 25px !important;
        }

        td {
            font-family: 'Poppins', sans-serif;
        }

        h4 {
            text-align: center !important;
            margin-top: 20px !important;
            margin-bottom: 20px !important;
            font-size: 14px;
            font-weight: 400 !important;
            color: rgb(113, 128, 150) !important;
            font-family: 'Poppins', sans-serif;
            padding-left: 25px !important;
            padding-right: 25px !important;
        }
      
        
    </style>
</head>
<body>
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td class="main">
                        <div>
                            <img alt='{{ $companyName }}' 
                                style="width:100px;margin:0 auto;display:block;margin-top:40px;margin-bottom:40px;"
                                src="{{ $companyLogo }}">
                        </div>
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <h2>Hej {{ $userName }}</h2>
                                    <h2>Vejralarm for {{ $job->title }}</h2>
                                    <p>Vær opmærksom på at vejrudsigten for d. {{ $date }}</p>
                                    <p>melder om: {{ implode(', ', $alertForecast['alert_reasons']) }}</p>
                                    <p>Temperatur: {{ $tempString }}</p>
                                    <h2><a href="{{ url('/jobs/'.$job->id) }}">Gå til opgave</a></h2>
                                    <h4>Dette er en autogeneret mail fra DIT-System</h4>
                                </td>
                            </tr>
                        </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
