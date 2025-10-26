<?php
/**
* Do not allow to frame simpleSAMLphp pages from another location.
* This prevents clickjacking attacks in modern browsers.
*
* If you don't want any framing at all you can even change this to
* 'DENY', or comment it out if you actually want to allow foreign
* sites to put simpleSAMLphp in a frame. The latter is however
* probably not a good security practice.
*/
header('X-Frame-Options: DENY');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
    <title><?php echo $this->configuration->getString('application_title')?></title>

    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">

    <meta name="robots" content="noindex, nofollow" />
    <meta name="googlebot" content="noarchive, nofollow" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="icon" type="image/png" href="/<?php echo $this->data['baseurlpath']; ?>resources/buildspacetheme1/favicon.png"/>

    <link href="https://fonts.googleapis.com/css?family=Lato|Open+Sans|PT+Sans|Roboto|Roboto+Slab|Titillium+Web" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/<?php echo $this->data['baseurlpath']; ?>resources/buildspacetheme1/css/main.min.css">
    <link rel="stylesheet" type="text/css" href="/<?php echo $this->data['baseurlpath']; ?>resources/buildspacetheme1/css/client_style.css">

    <style>
        body {
            background: linear-gradient(70deg, rgb(77, 187, 255), rgb(22, 247, 198));
        }

        div.background:before {
            background-color: <?php echo (! empty($this->data['theme_colour1'])) ? $this->data['theme_colour1'] : '#03A9F4'; ?>;
            border-bottom: <?php echo (! empty($this->data['theme_colour2'])) ? '30px solid '.$this->data['theme_colour2'] : '30px solid #2196F3'; ?>;
            /*border-bottom: 30px solid hsl(6, 100%, 38%);*/
        }

        .main-container, .logo-container, .powered {
            background: white;
        }

        .main-container {
            /*display: flex;
            flex-direction: row;*/
            align-items: center;
            justify-content: center;
            border-radius: 15px; /* Add rounded edges */
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }

        .login-container {
            text-align: center;
        }

        .image-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            overflow: hidden; /* Ensure that the image doesn't overflow the container */
            margin: 0 auto;
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Options: cover, contain, fill, none, scale-down */
        }

        .login-img-container {
            max-width: 600px;
            max-height: 450px;
        }

        .logo-container {
            max-width: 275px;
            max-height: 92px;
            /*background: <?php echo (!empty($this->data['theme_colour1'])) ? $this->data['theme_colour1'] : '#2196F3'; ?>;*/
        }

        /*.powered {
            background: <?php echo (! empty($this->data['theme_colour1'])) ? $this->data['theme_colour1'] : '#2196F3'; ?>;
        }*/
        .powered a {
            color: #8e8a8a;
        }
        .powered p {
            margin: 0;
            padding: 0;
        }

        .btn-bluesky
        {
            background-color: <?php echo (! empty($this->data['theme_colour1'])) ? '#fff' : '#03a9f4'; ?>;
            color: <?php echo (! empty($this->data['theme_colour1'])) ? $this->data['theme_colour1'] : '#ecf0f1'; ?>;
            border-color: <?php echo (! empty($this->data['theme_colour1'])) ? '#fff' : '#03a9f4'; ?>;
        }

        .buttonui {
            background-color: <?php echo (! empty($this->data['theme_colour1'])) ? '#fff' : '#03a9f4'; ?>;
            color: <?php echo (! empty($this->data['theme_colour1'])) ? $this->data['theme_colour1'] : '#ecf0f1'; ?>;
        }

        .buttonui:hover {
            background-color: <?php echo (! empty($this->data['theme_colour1'])) ? $this->data['theme_colour1'] : '#03a9f4'; ?>;
            cursor: pointer;
        }

        .pipe-divider {
            color: black;
            font-size: 12px;
        }

        .footer-bar {
            position: fixed !important;
            left: 0 !important;
            bottom: 0 !important;
            height: 28px !important;
            width: 100% !important;
            border-top: 1px solid #e5e5e5 !important;
            overflow: hidden !important;
            display: flex !important;
            align-items: center !important;
        }

        .footer-bar .footer {
            padding-top: 0 !important;
            font-size: 9px !important;
            white-space: nowrap !important;
            line-height: 0 !important;
        }

        .footer-bar .footer ul {
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .footer-bar .footer ul li {
            color: #656060 !important;
            display: inline !important;
        }
    </style>

    <noscript>
        <meta http-equiv="refresh" content="0; url=<?php echo $this->configuration->getString('path_eproject') ?>/no-script">
        <style>body{opacity:0;}</style>
    </noscript>
</head>
<body>
    <div class="container-fluid">
        <div class="row justify-content-center min-vh-100">
            <div class="p-2 mr-10">
                <?php if($this->configuration->getBoolean('view_tenders')): ?>
                    <a class="btn btn-primary btn-bluesky float-start float-md-end" href="<?php echo $this->configuration->getString('path_eproject') ?>/project-main">View Tenders</a>
                <?php else: ?>
                    <button type="button" class="btn btn-primary btn-bluesky float-start float-md-end" style="display:none;">View Tenders</button>
                <?php endif;?>
            </div>

            <div class="col-12 col-lg-10 col-xl-8 col-xxl-8">
                <div class="main-container p-4">
                    <div class="row">
                        <div class="d-none d-lg-flex col-lg-6 col-xl-7 justify-content-center">
                            <div class="image-container login-img-container">
                                <img src="<?php echo $this->data['login_img']; ?>">
                            </div>
                        </div>

                        <div class="col-12 col-lg-6 col-xl-5">
                            <div class="login-container">
                                <div class="image-container logo-container">
                                    <img src="<?php echo $this->data['company_logo']; ?>">
                                </div>
                                <form name="loginform" id="loginform" action="?" method="post">
                                    <div class="group mb-3">
                                        <input type="email" name="username" id="username" <?php if (!empty($this->data['username'])) {
                                            echo 'class="used" value="' . htmlspecialchars($this->data['username']) . '"';} ?> size="20" required/>
                                        <span class="highlight"></span>
                                        <span class="bar"></span>
                                        <label><?php echo $this->t('{login:username}'); ?></label>
                                    </div>
                                    <div class="group mb-3">
                                        <input type="password" name="password" id="user_pass" value="" required/>
                                        <span class="highlight"></span>
                                        <span class="bar"></span>
                                        <label><?php echo $this->t('{login:password}'); ?></label>
                                    </div>
                                    <button type="submit" class="btn buttonui w-100 mb-3">
                                        <span> <?php echo $this->t('{login:login_button}'); ?> </span>
                                        <div class="ripples buttonRipples"><span class="ripplesCircle"></span></div>
                                    </button>

                                    <?php
                                    foreach ($this->data['stateparams'] as $name => $value) {
                                        echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
                                    }
                                    ?>
                                </form>
                                <div class="powered mt-3">
                                    <?php if ($this->data['errorcode'] !== NULL):?>
                                    <p><?php echo $this->t('{errors:title_' . $this->data['errorcode'] . '}'); ?></p>
                                    <?php endif;?>
                                    <?php if($this->configuration->getBoolean('sign_up_page')): ?>
                                    <a href="<?php echo $this->configuration->getString('path_eproject') ?>/register">Sign up</a>&nbsp;<span class="pipe-divider">|</span>
                                    <?php endif ?>
                                    <a href="<?php echo $this->configuration->getString('forgot_password_link'); ?>">Forgot Password?</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer-bar">
                    <div class="footer content">
                        <ul id="footer-list">
                            <li>BuildSpace eProject <?php echo $this->configuration->getString('version') ?> © <?php echo date('Y')?>. All rights reserved.</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="/<?php echo $this->data['baseurlpath']; ?>resources/buildspacetheme1/js/main.min.js"></script>
</body>
</html>