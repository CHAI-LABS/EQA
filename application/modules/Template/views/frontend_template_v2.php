<?php $assets_url = $this->config->item('assets_url'); ?>

<!DOCTYPE html>
<!--[if IE 9]>
<html class="ie" lang="en">
<![endif]-->
<html lang="zxx">


<!-- Mirrored from www.styllustheme.com/ThemeUnit/thirdeye-preview/thirdeye/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 13 Apr 2019 11:04:22 GMT -->
<head>
    <!-- Basic Page Needs  -->
    <meta charset="utf-8">
    <title>EQA</title>
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Mobile Specific Mega  -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="<?= @$assets_url; ?>home/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= @$assets_url; ?>home/css/font-awesome.min.css">
    <!--Owl Carousel CSS -->
    <link rel="stylesheet" href="<?= @$assets_url; ?>home/css/owl.carousel.min.css">
    <!-- Animated CSS -->
    <link rel="stylesheet" href="<?= @$assets_url; ?>home/css/animate.min.css">
    <!-- Preloader Css -->
    <link rel="stylesheet" href="<?= @$assets_url; ?>home/css/csspin.css">
    <!-- Theme CSS-->
    <link rel="stylesheet" href="<?= @$assets_url; ?>home/css/default.css">
    <link rel="stylesheet" href="<?= @$assets_url; ?>home/css/style.css">
    <link rel="stylesheet" href="<?= @$assets_url; ?>home/css/responsive.css">
    <!-- Favicon -->
    <!-- <link rel="shortcut icon" type="image/png" href="<?= @$assets_url; ?>home/img/favicon.png"> -->
    <?= @$page_css; ?>
</head>

<body data-spy="scroll" data-target="#scroll-menu" data-offset="65">
    <!-- Preloader Starts -->
    <div class="preloader-wrap">
        <div class="preloader-inside">
            <div class="cp-spinner cp-meter"></div>>
        </div>
    </div>
    <!-- Preloader Ends -->
    <!-- Nav Section -->
    <header>
        <!-- Nav Section -->
        <nav class="navbar navbar-default navbar-fixed-top nav-area" id="scroll-menu">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="<?= @base_url('Home'); ?>">
                        <img height="50px" width="50px" src="<?= @$assets_url; ?>frontend/images/files/gok.png" alt="">
                        
                        <!-- <img src="<?= @$assets_url; ?>home/img/logo.png" alt="responsive img"> -->
                    </a>
                    <a  class="navbar-brand" href="<?= @base_url('Home'); ?>">
                        <img height="60px" width="180px" src="<?= @$assets_url; ?>frontend/images/files/ministry.png" alt="">
                    </a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <!-- <li class="active"><a href="<?= @base_url('Home'); ?>" class="smoothscroll">Home</a></li> -->
                        <li class="active"><a href="<?= @base_url('Home'); ?>">Home</a></li>
                        <li><a href="#about-us" class="smoothscroll">About us</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Library</a>
                            <ul class="dropdown-menu">
                                <li><a download href="<?= @$sop_path; ?>">SOPs</a></li>
                                <li><a href="<?= @base_url('Home/FAQ'); ?>">FAQs</a></li>
                                <li><a download href="/uploads/docs/EXTERNAL_QUALITY_ASSURANCE_USER_GUIDE.pdf">User Guide</a></li>
                                <!-- <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sub Menu</a>
                                    <ul class="dropdown-menu sub-menu">
                                        <li><a href="#">Sub Menu 1</a></li>
                                        <li><a href="#">Sub Menu 2</a></li>
                                    </ul>
                                </li> -->
                            </ul>
                        </li>
                        <li><a href="#contact" class="smoothscroll">Contact us</a></li>
                    </ul>
                    <div class="nav navbar-nav navbar-right hidden-xs hidden-sm">
                        <!-- <form action="#" method="POST"> -->
                            <div class="form-group">
                                <!-- <div class="input-group"> -->
                                    <!-- <input type="text" placeholder="Search...." class="form-control placeholder" id="text-mail2"> -->
                                    <span class="input-group-btn">
                                        <a href="<?= @base_url('Auth/signin'); ?>" class="btn btn-default">Login</a>
                                    </span>
                                <!-- </div> -->
                            </div>
                        <!-- </form> -->
                    </div>
                </div>
                <!--/.nav-collapse -->
            </div>
        </nav>
    </header>
    <!-- End Nav Section -->
    <!-- Hero Section -->

    <?= $this->load->view($partial, $partialData); ?>

    <!-- Footer Section -->
    
    <!-- Ends Footer Section -->
    <!-- Copyright Section -->
    <div class="copyright-area">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <p class="footer-copyright">EQA Copyright © 2019. All Rights Reserved</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Ends Copyright Section -->
    <!-- Scripts -->
    <script src="<?= @$assets_url; ?>home/js/jquery-2.1.1.min.js"></script>
    <script src="<?= @$assets_url; ?>home/js/bootstrap.min.js"></script>
    <script src="<?= @$assets_url; ?>home/js/scrollreveal.min.js"></script>
    <script src="<?= @$assets_url; ?>home/js/jquery.waypoints.min.js"></script>
    <script src="<?= @$assets_url; ?>home/js/jquery.counterup.min.js"></script>
    <script src="<?= @$assets_url; ?>home/js/owl.carousel.min.js"></script>
    <script src="<?= @$assets_url; ?>home/js/theme.js"></script>
    <?= @$page_js; ?>
</body>


</html>
