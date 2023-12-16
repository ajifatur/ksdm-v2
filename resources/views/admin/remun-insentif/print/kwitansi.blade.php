<html>
<head>
    <title>{{ $title }}</title>
    <style>
		/* Font */
		@font-face {font-family: 'FiraSans-Regular'; src: url({{ public_path('assets/fonts/Fira_Sans/FiraSans-Regular.ttf') }});}
		@font-face {font-family: 'FiraSans-Bold'; src: url({{ public_path('assets/fonts/Fira_Sans/FiraSans-Bold.ttf') }});}

        /* Page */
		@page {margin: 0px;}
		html {margin: 0px;}
		body {font-family: 'FiraSans-Regular'; font-size: 15px; line-height: 15px;}
		.page-break {page-break-after: always;}
		
		/* Content */
		#content {position: absolute; text-align: justify;}
		
		/* Subject */
        #subject {position: absolute; right: 15px;}
		#subject tr td {vertical-align: top;}
    </style>
</head>
<body>
    <table id="subject">
        <tr>
            <td width="50">TA</td>
            <td width="5">:</td>
            <td>2023</td>
        </tr>
        <tr>
            <td>Nomor Bukti</td>
            <td>:</td>
            <td></td>
        </tr>
    </table>
</body>
</html>