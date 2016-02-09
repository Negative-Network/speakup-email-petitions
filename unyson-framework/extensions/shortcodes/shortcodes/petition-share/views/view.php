
<?php

$fb = $twitter = $mail = '';

$petition = new dk_speakup_Petition();

$id = 0; // default
if ( isset( $atts['petition_id'] ) && is_numeric( $atts['petition_id'] ) ) {
	$id = $atts['petition_id'];
}
$petition_exists = $petition->retrieve( $id );
if ( $petition_exists ) {

        $name_origin = $petition->title;
        $name_clean = preg_replace(array('/Ä/', '/Ö/', '/Ü/', '/ä/', '/ö/', '/ü/', '/ß/'), array('Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue','ss'), $petition->title);
        $name_url = urlencode($petition->share_fb_title);

        $twitter_name = $petition->share_twitter;

        $url = get_permalink(  );
        $image_url = $petition->share_fb_img;



        $fb = '<a title="Partager sur Facebook" href="http://www.facebook.com/sharer.php?s=100&p[title]=' . $name_url . '&p[summary]=' . urlencode($petition->share_fb_desc) . '&p[url]=' . $url . '&p[images][0]=' . $image_url . '" target="_blank">
        <img src="'.get_bloginfo('stylesheet_directory').'/images/bouton-facebook.png" alt="Icône Facebook" /> </a>';
        
        $twitter = '<a title="Partager sur Twitter" href="https://twitter.com/share?url=' . $url . '&amp;text=' . urlencode($petition->share_twitter) . ' - " target="_blank"> 
        <img src="'.get_bloginfo('stylesheet_directory').'/images/bouton-tweet.png" alt="Icône Twitter" /> </a>';
        
        $mail = '<a title="Partager par email" href="mailto:?subject=' . $petition->share_email_subject . '&amp;body=' . $petition->share_email_body .'">
        <img src="'.get_bloginfo('stylesheet_directory').'/images/bouton-mail.png"  alt="Icône Email" /></a></a>';
}

?>

<div class="share large-12 columns center">
	<h3><?php echo $petition->twitter_message!=''?$petition->twitter_message:'Partagez cette pétition'; ?></h3>
	<ul>
        	<li><?php echo $fb; ?></li>
        	<li><?php echo $twitter; ?></li>
        	<li><?php echo $mail; ?></li>
	</ul>
</div>