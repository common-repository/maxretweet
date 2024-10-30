<?php
function maxretweet_plugin_url( $path = '' ) {
	return plugins_url( $path, MRTWEET_PLUGIN_BASENAME );
}
