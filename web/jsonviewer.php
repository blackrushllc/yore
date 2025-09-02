<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"  "http://www.w3.org/TR/html4/loose.dtd">
<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Online JSON Viewer and Formatter</title>
    <meta name="description" content="JSON Viewer and Formatter - Convert JSON Strings to a Friendly Readable Format">
    <meta name="keywords" content="json, json viewer, json formatter">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="extjs/css/ext-gray-all.css" media="all">


    <script>
        function page_begin()
        {

        }
    </script>

    <script type="text/javascript" src="jsonviewer-all.js?v7"></script>

    <script>
        function show_form()
        {
            document.getElementById("footer").style.display = "none";
            document.getElementById("footer_form").style.visibility = "visible";
        }

        function valid_input()
        {
            var re3 = /^\s*([a-zA-Z0-9\._\-\+]{1,100})@([a-zA-Z0-9\.\-_]){1,100}\.([a-zA-Z]{2,7})\s*$/gi;

            email = document.getElementById("bemail");


            emailvalue = email.value.toLowerCase();
            var pos1 = emailvalue.indexOf("test");
            var pos2 = emailvalue.indexOf("myemail@gmail.com");
            if(pos1>=0 || pos2>=0)
            {
                alert('Please enter a valid email address');
                return false;
            }


            if (email.value.length>=0 && !email.value.match(re3))
            {
                alert("Please enter a valid email address");
                email.focus();
                return false;
            }

            return true;
        }
    </script>

    <style type="text/css">
        .fform{
            display: inline-block;
            width:700px;
            text-align: left;
            margin-top:5px;
        }
        .fform a, .fform a:visited, .fform a:hover
        {
            color: #0070e0;
        }
        .fform a:hover
        {
            color: #142c8e;
        }

        .myinput, .mybutton, .getbutton, .paybutton{
            float:left;
            padding:13px 8px;
            font-size:18px;
            border-radius: 5px;
            text-align: left;
            margin:3px 5px 0 0;

            -webkit-appearance:none;
            -moz-appearance:none;
            -ms-appearance:none;
            -o-appearance:none;
            appearance:none;
        }
        .mybutton, .getbutton, .paybutton{
            width:195px;
            cursor: pointer;
            color:white;
            background-color:#f1641e;
            border:1px solid white;
            text-align: center;
        }
        .mybutton:hover, .getbutton:hover, .paybutton:hover{
            border:1px solid white;

            background: rgb(254,172,94);
            background: -moz-linear-gradient(90deg, rgba(254,172,94,1) 0%, rgba(199,121,208,1) 50%, rgba(75,192,200,1) 100%);
            background: -webkit-linear-gradient(90deg, rgba(254,172,94,1) 0%, rgba(199,121,208,1) 50%, rgba(75,192,200,1) 100%);
            background: linear-gradient(90deg, rgba(254,172,94,1) 0%, rgba(199,121,208,1) 50%, rgba(75,192,200,1) 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#feac5e",endColorstr="#4bc0c8",GradientType=1);
        }
        .myinput{
            width:400px;
            border:1px solid #f1641e;
        }
        .smalltext{
            float:left;
            width:450px;
            font-size:14px;
            margin-top:5px;
            line-height: 16px;
        }

        .footer, .footer_form
        {
            position: fixed;
            bottom: 0px;
            width: 100%;
            text-align: center;
            font-family: Arial;

            /*
            background: rgb(254,172,94);
            background: -moz-linear-gradient(90deg, rgba(254,172,94,1) 0%, rgba(199,121,208,1) 50%, rgba(75,192,200,1) 100%);
            background: -webkit-linear-gradient(90deg, rgba(254,172,94,1) 0%, rgba(199,121,208,1) 50%, rgba(75,192,200,1) 100%);
            background: linear-gradient(90deg, rgba(254,172,94,1) 0%, rgba(199,121,208,1) 50%, rgba(75,192,200,1) 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#feac5e",endColorstr="#4bc0c8",GradientType=1);
            */
            background:linear-gradient(180deg,transparent,hsla(0,0%,95%,.288) 47.39%),linear-gradient(89.84deg,rgba(230,36,174,.15) .14%,rgba(94,58,255,.15) 16.96%,rgba(10,136,255,.15) 34.66%,rgba(75,191,80,.15) 50.12%,rgba(137,206,0,.15) 66.22%,rgba(239,183,0,.15) 82%,rgba(246,73,0,.15) 99.9%),linear-gradient(0deg,hsla(0,0%,100%,.15),hsla(0,0%,100%,.15));
        }
        .footer{
            padding: 2px 0;	}
        .footer_form
        {

            visibility: hidden;

            padding: 2px 0;
            height:140px;
        }
        .support
        {

            height:20px;
            color: #222;
            /*
            background: linear-gradient(to right, orange , yellow, green, cyan, blue, violet);
              -webkit-background-clip: text;
              -webkit-text-fill-color: transparent;
              font-size: 16px;
              line-height: 16px;
              */
        }
        .support a, .support a:visited, .support a:hover
        {
            color: #00cc66;
            font-weight: bold;
        }
        .support a:hover
        {
            color: #ffa400;
        }

        a.logout, a.logout:visited, a.logout:hover
        {
            color: black;
            text-decoration:none;
        }
        a.logout:hover
        {
            color: #ff0000;
        }

        .error
        {
            /*background-color:#fce0e0;*/
            color:#f14443;
            font-weight: bold;
        }
        .success
        {
            /*background-color:#d8f2e6;*/
            color:#0ab56b;
            font-weight: bold;
        }

        .heart{
            width: 16px;
            height: 16px;
            vertical-align: -2px;
            fill: #f00;
        }

        .br0 {color:#090} .st0 {color:#36C} .sy0 {color:#393} .x-viewport,.x-viewport body{height:calc(100% - 72px);} body{background-color:#ededed;}
    </style>
</head>

<body onLoad="page_begin();">
<h1 style="margin-top:150px;">Online JSON Viewer</h1>

<div class="tab">
    <h2>About JSON</h2>
</div>

<div class="tab">
    <h2>Example</h2>

</div>

<div class="tab">
    <h2>About Online JSON Viewer</h2>
    <div>

    </div>
</div>


<div class="footer_form" id="footer_form">

</div>

<div class="footer" id="footer">


</div>


</body></html>