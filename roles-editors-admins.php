<?php
/*
Plugin Name: Restricted posts comments and library
Plugin URI: http://sohag07hasan.0fees.net
Description: This plugin protects posts,comments,media library of one author from another.
* Basically it helps you maintain multi author blogs
Author: sohag hasan
Version: 1.0
Author URI: http://sohag07hasan.0fees.net
*/

	if(!class_exists('comment_modification')):
		class comment_modification{
			function modification($content){
				
				global $current_user;	
				global $wpdb;
							
				$plugin_user_id = $current_user->data->ID;

				//checking if the user is author
				if (!current_user_can('edit_private_posts')){
					
						$post_id = array();
						$user_id = array();
					foreach($content->items as $items){
						$post_id[] = $items->comment_post_ID;
						$user_id[] = $wpdb->get_var($wpdb->prepare("SELECT post_author FROM $wpdb->posts WHERE ID = %d ", $items->comment_post_ID));
											
					}
					// unsetting
					
					for($i=0;$i<count($user_id);$i++){
						if($user_id[$i] != $plugin_user_id){
							unset($content->items[$i]);
							unset($content->pending_count[$post_id[$i]]);
						}
					}
					
				}
				return $content;
		
			}
			
			/********************************************************************************
			 * 			ADDING JQUERY TO MANIPULATE ADMIN COMMENTS SESSION
			 * ********************************************************************************
			 * */
			function adding_js(){
				
				if (!current_user_can('edit_private_posts')){
					wp_enqueue_script('jquery');
					//wp_enqueue_script('myjquery_sohag',plugins_url('/voting-machine/js/voting.js'));
					// embed the javascript file that makes the AJAX request
					
					wp_enqueue_script( 'myjquery_sohag_comment',plugins_url('/roles-editors-comments/js/comments.js'),array('jquery'));
					$nonce=wp_create_nonce('comments-editing');
					 
					// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
					wp_localize_script( 'myjquery_sohag_comment', 'CommentAjax', array( 
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'commentnonace' => $nonce,
						'pluginsurl' => plugins_url('/roles-editors-comments/js/comments.js'),
					));
				}
			
			}
			
			/*****************************************************************************************************
			 * 
			 * 					ADDING EXTERNAL CSS
			 * ***************************************************************************************************
			 * **/
			
			
			function adding_css(){
				if (!current_user_can('edit_private_posts')){
					wp_register_style('restriction_style_css',plugins_url('/roles-editors-comments/css/style.css'));
					wp_enqueue_style('restriction_style_css');
				}
			}
			
			
			/**********************************************************************************************
								MY AJAX DATA
			 * *******************************************************************************************
			 * */
			function ajax_comment(){
				//sending data as xml format
								
				// generate XML header
				/*echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';*/
				// create the <response> element
				echo '<response>';
												
				global $current_user;
				global $wpdb;
				$plugin_user_id = $current_user->data->ID;
				
				//post_ids are fetching
				
				$post_ids = $wpdb->get_col($wpdb->prepare("SELECT comment_post_ID FROM $wpdb->comments WHERE comment_approved = %d ",0));
				$post_ids_spam = $wpdb->get_col($wpdb->prepare("SELECT comment_post_ID FROM $wpdb->comments WHERE comment_approved = %s ",'spam'));
				$post_ids_trash = $wpdb->get_col($wpdb->prepare("SELECT comment_post_ID FROM $wpdb->comments WHERE comment_approved = %s ",'trash'));
				$author_ids = array();
				$author_trash = array();
				$author_spam = array();
				
				
				//fetching authors id				
				foreach($post_ids as $item){
					$author_ids[] = $wpdb->get_var($wpdb->prepare("SELECT post_author FROM $wpdb->posts WHERE ID = %d ", $item));
				}
				
				foreach($post_ids_trash as $item){
					$author_trash[] = $wpdb->get_var($wpdb->prepare("SELECT post_author FROM $wpdb->posts WHERE ID = %d ", $item));
				}
				
				$trash = 0;
				for($i=0;$i<count($author_trash);$i++){
					if($author_trash[$i] == $plugin_user_id){
						$trash = $trash + 1;
					}
				}
								
				
				
				foreach($post_ids_spam as $item){
					$author_spam[] = $wpdb->get_var($wpdb->prepare("SELECT post_author FROM $wpdb->posts WHERE ID = %d ", $item));
				}
				
				$spam = 0;
				for($i=0;$i<count($author_spam);$i++){
					if($author_spam[$i] == $plugin_user_id){
						$spam = $spam + 1;
					}
				}
				
				//$attachment_ids = $post_ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_author = $plugin_user_id "));
				$attachment_ids = $post_ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_author != $plugin_user_id"));
				//$attachment_ids = $post_ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_author = $plugin_user_id"));
				
				//counting unapproved comments number
				$total = 0;
				for($i=0;$i<count($author_ids);$i++){
					if($author_ids[$i] == $plugin_user_id){
						$total = $total + 1;
					}
				}
				
				$library = array();
				for($i=0;$i<count($attachment_ids);$i++){
					$library[] = 'post-'.$attachment_ids[$i];
							
				}
				$library[count($attachment_ids)] = 'total'.$total;
				$library[count($attachment_ids)+1] = 'trash'.$trash;
				$library[count($attachment_ids)+2] = 'spam'.$spam;
				for($i=0;$i<count($library);$i++){
					echo '<postid>'.$library[$i].'</postid>';
				}
				
				echo '</response>';
							
				exit;					
				
			}
			
						
		}
	
		$com_mod = new comment_modification();
		//add_action('admin_print_scripts',array($com_mod,'adding_js'),50);
		add_action('admin_enqueue_scripts',array($com_mod,'adding_js'),12);
		add_action('admin_enqueue_scripts',array($com_mod,'adding_css'),0);
		
		add_action('wp_ajax_myajax_data_comment',array($com_mod,'ajax_comment'));
		add_action('wp_ajax_nopriv_myajax_data_comment',array($com_mod,'ajax_comment'));
		//add_action('myajax_data_comment',create_function('','echo "testing";exit;'));//array($com_mod,'ajax_comment'));
		add_filter('personal_comment_filter',array($com_mod,'modification'),1);
		
	endif;
?>
