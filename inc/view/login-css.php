<style type="text/css">
    body{
        background: #f1f1f1;
    }
    input[type="submit"]:hover{
        cursor: pointer;
    }
    .login_content{
        margin-left:auto;
        margin-right:auto;
        margin-top:30px;
        width:600px;
        padding: 26px 24px 46px;
        padding-left: 49px;
        font-weight: 400;
        overflow: hidden;
        background: #fff;
        -webkit-box-shadow: 0 1px 3px rgba(0,0,0,.13);
        box-shadow: 0 1px 3px rgba(0,0,0,.13);
    }
    #im_authform td,
    #im_authform th{
        border: none;
    }
    #im_authform .form_tag{
        border-bottom: 1px solid #cbcbcb;
        padding-bottom: 15px;
        margin-bottom:20px;
        font-size: 25px;
        font-weight: normal;
    }
    #im_authform label{
        margin-bottom:5px;
        display: block;
    }
    #login_form{
        margin-right: 50px;
    }
    #login_form input[type="text"],
    #login_form input[type="password"],
    #register input[type="text"],
    #register input[type="password"]{
        border: 1px solid #cbcbcb;
        height: 40px;
        width: 250px;
        background-color: #f2f2f2;
        padding-left: 5px;
        margin-bottom: 10px;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
    }
    #login_form .submit,
    #register .submit{
        background: url('<?php echo plugins_url('images/login.png', __DIR__); ?>') no-repeat;
        border: none;
        width: 103px;
        height: 38px;
        font-size: 14px;
        color:#fff;
    }
    #login_form{
        float: left;
        margin-bottom: 20px;
    }
    .g-recaptcha div{
        width: 244px !important;
    }
</style>