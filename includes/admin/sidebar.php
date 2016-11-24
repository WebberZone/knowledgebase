<?php
/**
 * Sidebar
 *
 * @link  https://webberzone.com
 * @since 1.0.0
 *
 * @package    WZKB
 * @subpackage Admin/Footer
 */

?>
<div class="postbox-container">
	<div id="donatediv" class="postbox meta-box-sortables">
	  <h2 class='hndle'><span><?php _e( 'Support the development', 'knowledgebase' ); ?></span></h3>
	  <div class="inside" style="text-align: center">
		<div id="donate-form">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_xclick">
			<input type="hidden" name="business" value="donate@ajaydsouza.com">
			<input type="hidden" name="lc" value="IN">
			<input type="hidden" name="item_name" value="<?php _e( 'Donation for Knowledgebase', 'knowledgebase' ); ?>">
			<input type="hidden" name="item_number" value="crp_plugin_settings">
			<strong><?php _e( 'Enter amount in USD:', 'knowledgebase' ); ?></strong> <input name="amount" value="10.00" size="6" type="text"><br />
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="button_subtype" value="services">
			<input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php _e( 'Send your donation to the author of', 'knowledgebase' ); ?> Knowledgebase?">
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div><!-- /#donate-form -->
	  </div><!-- /.inside -->
	</div><!-- /.postbox -->

	<div id="qlinksdiv" class="postbox meta-box-sortables">
	  <h2 class='hndle metabox-holder'><span><?php _e( 'Quick links', 'knowledgebase' ); ?></span></h3>
	  <div class="inside" style="text-align: center">
	    <div id="quick-links">
			<ul class="subsubsub">
				<li><a href="https://webberzone.com/plugins/knowledgebase/"><?php _e( 'Knowledgebase plugin homepage', 'knowledgebase' ); ?></a>| </li>
				<li><a href="https://wordpress.org/plugins/knowledgebase/faq/"><?php _e( 'FAQ', 'knowledgebase' ); ?></a>| </li>
				<li><a href="http://wordpress.org/support/plugin/knowledgebase"><?php _e( 'Support', 'knowledgebase' ); ?></a>| </li>
				<li><a href="https://wordpress.org/support/view/plugin-reviews/knowledgebase"><?php _e( 'Reviews', 'knowledgebase' ); ?></a>| </li>
				<li><a href="https://github.com/WebberZone/knowledgebase"><?php _e( 'Github repository', 'knowledgebase' ); ?></a>| </li>
				<li><a href="https://webberzone.com/plugins/"><?php _e( 'Other plugins', 'knowledgebase' ); ?></a>| </li>
				<li><a href="https://ajaydsouza.com/"><?php _e( "Ajay's blog", 'knowledgebase' ); ?></a></li>
			</ul>
	    </div>
	    <br />&nbsp;
	  </div><!-- /.inside -->
	</div><!-- /.postbox -->
</div>

<div class="postbox-container">
	<div id="followdiv" class="postbox meta-box-sortables">
	  <h2 class='hndle'><span><?php _e( 'Follow me', 'knowledgebase' ); ?></span></h3>
	  <div class="inside" style="text-align: center">
		<div id="twitter">
			<div style="text-align:center"><a href="https://twitter.com/WebberZoneWP" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @WebberZoneWP</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>
		</div>
		<div id="facebook">
			<div id="fb-root"></div>
			<script>
			//<![CDATA[
				(function(d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.4&appId=458036114376706";
				fjs.parentNode.insertBefore(js, fjs);
				}(document, 'script', 'facebook-jssdk'));
			//]]>
			</script>
			<div class="fb-page" data-href="https://www.facebook.com/WebberZone" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="false" data-show-posts="false"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/WebberZone"><a href="https://www.facebook.com/WebberZone">WebberZone</a></blockquote></div></div>
		</div>
	  </div><!-- /.inside -->
	</div><!-- /.postbox -->
</div>
