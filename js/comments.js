jQuery(document).ready(function(){
	
		jQuery("ul.subsubsub > li.all ").html(null);			
		jQuery("ul.subsubsub > li.publish").html(null);
			
		var attachments = new Array();		
		
		jQuery.ajax(
			{
			
				type:'post',
				url:CommentAjax.ajaxurl,
				timeout:10000,
				cache:false,
				dataType: "html",
				data:{
					'action':'myajax_data_comment',
					'requestNonce':CommentAjax.commentnonace,
				},
				
				success:function(result){
					
					//alert(result);
				
					var str = String(result);
 									
					elemArray=new Array();

					//str returned by ajax
					regGet(str);
			
					
					function regGet(str){	
						var reg=/<postid>+.[^\/<]+<\/postid>/;
						var reg1=/<postid>|<\/postid>/g;
						if(reg.test(str)){
							var todoStr=reg.exec(str);
							todoStr=String(todoStr);
							//alert(todoStr);
							elemArray[elemArray.length]=String(todoStr.replace(reg1,''));
							if(reg.test(str.replace(reg,'')))regGet(str.replace(reg,''));
						}				
					}
					//alert(elemArray);
					//
					str1=str;
					str=str.replace(/\s/g,'');
				
				var commentno = elemArray[elemArray.length-3].replace('total','');
				var trash = elemArray[elemArray.length-2].replace('trash','');
				var spam = elemArray[elemArray.length-1].replace('spam','');
				
				
				for(i=0;i<elemArray.length-1;i++){
					var id = '#'+elemArray[i];					
					var media_id = '#'+'media-item'+elemArray[i].replace('post','');
					
					jQuery(id).html(null);
					jQuery(media_id).css({'display':'none'});
					jQuery(media_id).html(null);					
				}
													
					//now pending count is set to shown
					
					jQuery('#posts-filter').css({'display':'block'});
					
					//setting commnts lnk to shown
					var no = 0 + commentno - trash - spam;
					jQuery('.pending-count').html(no);
					jQuery('.spam-count').html(spam);
					jQuery('.trash-count').html(trash);
					jQuery('.pending-count').css({'display':'inline'});
					jQuery('ul.subsubsub > li.spam').css({'display':'inline'});
					jQuery('ul.subsubsub > li.trash').css({'display':'inline'});
					
					if(commentno != 0){										
						jQuery('#awaiting-mod').css({'display':'inline'});						
					}
					
					//showing media library
					jQuery('#media-upload').css({'display':'block'});	

				},
				
				error: function(jqXHR, textStatus, errorThrown){
					alert(textStatus);
					
				}
				
			}
		); 
		
});
