<?php
    require('instagram_basic_display_api.php');

    $params=array(
        'get_code'=> isset($_GET['code'])?$_GET['code']:'',
    );
    $ig=new instagram_basic_display_api( $params );

?>
<h1>Instagram Basic Display API</h1>
<?php if ($ig->_hasUserAccessToken): ?>
        <h3>Instagram Info</h3>
        <h4> Access token</h4>
        <?php echo $ig->getUserAccessToken(); ?>
        <h5> Expires in</h5>
        <?php echo ceil($ig->getUserAccessTokenExpires()/86400); ?> days
<?php else : ?>        
<a href="<?php echo $ig->_authorizationUrl; ?>">
    Authorize with Instagram
</a>    
<?php endif; ?>

