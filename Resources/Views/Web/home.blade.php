<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/earlyaccess/droidarabickufi.css" rel="stylesheet" type="text/css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
        body {
            font: 400 15px/1.8 'Droid Arabic Kufi', sans-serif;
            color: #777;
        }
        h3, h4 {
            margin: 10px 0 30px 0;
            /*letter-spacing: 10px;*/
            font-size: 20px;
            color: #111;
        }
        .container {
            padding: 8px 12px;
        }
        .person {
            border: 10px solid transparent;
            margin-bottom: 25px;
            width: 80%;
            height: 80%;
            opacity: 0.7;
        }
        .person:hover {
            border-color: #f1f1f1;
        }
        .carousel-inner img {
            /*-webkit-filter: grayscale(90%);*/
            /*filter: grayscale(90%); !* make all photos black and white *!*/
            width: 100%; /* Set width to 100% */
            margin: auto;
        }
        .carousel-caption h3 {
            color: #fff !important;
        }
        @media (max-width: 600px) {
            .carousel-caption {
                display: none; /* Hide the carousel text when the screen is less than 600 pixels wide */
            }
        }
        .bg-1 {
            background: #2d2d30;
            color: #bdbdbd;
        }
        .bg-1 h3 {color: #fff;}
        .bg-1 p {font-style: italic;}
        .list-group-item:first-child {
            border-top-right-radius: 0;
            border-top-left-radius: 0;
        }
        .list-group-item:last-child {
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }
        .thumbnail {
            padding: 0 0 15px 0;
            border: none;
            border-radius: 0;
        }
        .thumbnail p {
            margin-top: 15px;
            color: #555;
        }
        .btn {
            padding: 10px 20px;
            background-color: #333;
            color: #f1f1f1;
            border-radius: 0;
            transition: .2s;
        }
        .btn:hover, .btn:focus {
            border: 1px solid #333;
            background-color: #fff;
            color: #000;
        }
        .modal-header, h4, .close {
            background-color: #333;
            color: #fff !important;
            text-align: center;
            font-size: 30px;
        }
        .modal-header, .modal-body {
            padding: 40px 50px;
        }
        .nav-tabs li a {
            color: #777;
        }
        #googleMap {
            width: 100%;
            height: 400px;
            -webkit-filter: grayscale(100%);
            filter: grayscale(100%);
        }
        .navbar {
            font-family: 'Droid Arabic Kufi', Montserrat, sans-serif;
            margin-bottom: 0;
            background-color: #2d2d30;
            border: 0;
            font-size: 11px !important;
            /*letter-spacing: 4px;*/
            opacity: 0.9;
        }
        .navbar li a, .navbar .navbar-brand {
            color: #d5d5d5 !important;
        }
        .navbar-nav li a:hover {
            color: #fff !important;
        }
        .navbar-nav li.active a {
            color: #fff !important;
            background-color: #29292c !important;
        }
        .navbar-default .navbar-toggle {
            border-color: transparent;
        }
        .open .dropdown-toggle {
            color: #fff;
            background-color: #555 !important;
        }
        .dropdown-menu li a {
            color: #000 !important;
        }
        .dropdown-menu li a:hover {
            background-color: red !important;
        }
        footer {
            background-color: #2d2d30;
            color: #f5f5f5;
            padding: 15px;
        }
        footer a {
            color: #f5f5f5;
        }
        footer a:hover {
            color: #777;
            text-decoration: none;
        }
        .form-control {
            border-radius: 0;
        }
        textarea {
            resize: none;
        }
        .fa {
            font-size: 32px !important;
            padding: 0 10% !important;
        }
        .fa-facebook-official {
            /*background: #3B5998;*/
            color: #3B5998;
        }.fa-instagram {
             /*background: #125688;*/
             color: #125688;
         }
        .fa-snapchat {
            /*background: #fffc00;*/
            color: #fffc00;
            text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black;
        }

        #overlay {
            background: #ffffff;
            color: #666666;
            position: fixed;
            height: 100%;
            width: 100%;
            z-index: 5000;
            top: 0;
            left: 0;
            float: left;
            text-align: center;
            padding-top: 50%;
            opacity: .80;
        }
        .spinner {
            margin: 0 auto;
            height: 64px;
            width: 64px;
            animation: rotate 0.8s infinite linear;
            border: 5px solid firebrick;
            border-right-color: transparent;
            border-radius: 50%;
        }
        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        .underline_title{
            display: inline-block;
            width: 120px;
            height: 2px;
            background-color: #cccbcb;
            position: relative;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body id="myPage" data-spy="scroll" data-target=".navbar" data-offset="50" onload="off();">

<div id="overlay" style="display:flex;">
    <div class="spinner"></div>
</div>

@if(@$home->cover_images)
    <div id="myCarousel" class="carousel slide" data-ride="carousel">
        <!-- Indicators -->
        <ol class="carousel-indicators">
            @foreach($home->cover_images as $index => $image)
                <li data-target="#myCarousel" data-slide-to="{{$index}}" @if($index == 0) class="active" @endif ></li>
            @endforeach
        </ol>

        <!-- Wrapper for slides -->
        <div class="carousel-inner" role="listbox">
            @foreach($home->cover_images as $index => $image)
                <div class="item @if($index == 0) active @endif">
                    <img src="{{$image}}"  width="1200" height="700" style="height: 200px;object-fit: contain">
                    {{--            <div class="carousel-caption">--}}
                    {{--                <h3>New York</h3>--}}
                    {{--                <p>The atmosphere in New York is lorem ipsum.</p>--}}
                    {{--            </div>--}}
                </div>
            @endforeach


        </div>

        <!-- Left and right controls -->
        <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
            <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
            <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
@endif
<!-- Container (The Band Section) -->
<div id="band" class="container text-center">
    <h3>{{$home->name}}</h3>
    {{--    <p><em>We love music!</em></p>--}}
{{--    <img src="https://training.gymmawy.com/resources/assets/new_front/images/188.svg" alt="Me" class="w3-image w3-padding-32" style="width: 70%;padding-bottom: 20px">--}}
    <img src="{{$home->logo}}" alt="" class="w3-image w3-padding-32" style="width: 40%;padding-bottom: 20px">
    <p>{{strip_tags($home->about)}}</p>
    <br>

    <span class="underline_title"></span>
</div>



<!-- Container (Contact Section) -->
<div id="contact" class="container">
    <h3 class="text-center" >اتصل بنا</h3>
    <p class="text-center"><em>سعداء بتواصلك معنا</em></p>

    <div class="row">
        <div class="col-md-12">
            @if($home->address)<p><span class="glyphicon glyphicon-map-marker"></span> {{$home->address}} </p>@endif
            @if($home->phone)<p><span class="glyphicon glyphicon-phone"></span> {{$home->phone}} </p>@endif
            @if($home->email)<p><span class="glyphicon glyphicon-envelope"></span> {{$home->email}} </p>@endif
        </div>


    </div>
    <br/>
    <div class="row">
        <div  class="col-md-12 text-center">
            <!-- Add font awesome icons -->
            @if($home->facebook)<a href="{{$home->facebook}}" target="_blank"><i class="fa fa-facebook-official w3-hover-opacity"></i></a>@endif
            @if($home->instagram)<a href="{{$home->instagram}}" target="_blank"><i class="fa fa-instagram w3-hover-opacity"></i></a>@endif
            @if($home->snapchat)<a href="{{$home->snapchat}}" target="_blank"><i class="fa fa-snapchat w3-hover-opacity"></i></a>@endif

        </div>
    </div>
    <br>

</div>

<!-- Image of location/map -->
{{--<img src="https://www.w3schools.com/bootstrap/map.jpg" class="img-responsive" >--}}
<div class="ok-md-6 ok-xsd-12">
    <!-- MAP -->
    <div class="google-maps " style="width:100%">
        <iframe style="height: 420px" width="100%" height="500"
                id="gmap_canvas"
                src="https://maps.google.com/maps?q={{$home->latitude}},{{$home->longitude}}&t=&z=13&ie=UTF8&iwloc=&output=embed"
                frameborder="0" scrolling="no" marginheight="0"
                marginwidth="0"></iframe>
    </div>
    <!--! MAP -->
</div>
<!-- Footer -->
<footer class="text-center">
    <a class="up-arrow" href="#myPage" data-toggle="tooltip" title="TO TOP">
        <span class="glyphicon glyphicon-chevron-up"></span>
    </a><br><br>
    <p>{{trans('sw.developed_by')}} <a href="https://demo.gymmawy.com" data-toggle="tooltip" title="">gymmawy.com</a></p>
</footer>

<script>
    function on() {
        document.getElementById("overlay").style.display = "flex";
    }
    function off() {
        document.getElementById("overlay").style.display = "none";
    }
</script>

</body>
</html>


