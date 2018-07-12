<div class="objectif">

	<?php $signaturecount = (int) do_shortcode("[signaturecount id='".$atts['petition_id']."']"); ?>
	<?php $goal = (int) do_shortcode("[petitiongoal id='".$atts['petition_id']."']"); ?>
	<?php $percent = ( $signaturecount / $goal ) * 100; ?>

	  <h2>Objectif : <span class="orange"><?php echo $signaturecount; ?></span> / <?php echo $goal; ?> </h2>


	  <div class="progress-wrapper">
	    <div class="progress">
	      <span class="meter" style="width: <?php echo $percent; ?>%"><?php echo (int) $percent; ?> %</span>
	    </div>
	  </div>
	  
  </div>