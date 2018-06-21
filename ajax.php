<?php
include 'functions.php';

if ( $_POST['ajax_action'] == 'get_episode' ) 
{
	echo get_first_unseen_episode( $_POST['ajax_data']['show_url'] );
}