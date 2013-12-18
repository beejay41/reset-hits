<?php
/**
 * @package 	Plugin ResetHits for Joomla! 3.X
 * @version 	0.0.1
 * @author 	Function90.com
 * @copyright 	C) 2013- Function90.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
**/

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
  * Reset hits counter of an article 
  */
class plgSystemF90resethits extends JPlugin
{	
	public function onBeforeRender()
	{
		$app = JFactory::getApplication();	
		if($app->isSite()){
			return true;
		}

		$input 	= $app->input;	
		$option = $input->get('option', '');
		$view  	= $input->get('view', '');
		$layout = $input->get('layout', '');
		$id	= $input->get('id', '');
		if($option != 'com_content' || $view != 'article' || $layout != 'edit' || empty($id)){
			return true;
		}
		
		$doc = JFactory::getDocument();
		ob_start();
		?>
		(function($){
			$(document).ready(function(){
				$('<div id="#toolbar-reset-hits" class="btn-wrapper"><button id="f90-reset-hits" class="btn btn-small"><span class="icon-refresh"></span>Reset Hits</button></div>')
				.insertAfter('#toolbar-cancel');
				$('#f90-reset-hits').live('click', function(){
					if(confirm('Are you sure you want to reset hit counter of this article? This will hits to 0.')){
						$.ajax({
						       url: "index.php?plg=plg_f90_reset_hits&task=reset_hits&id=<?php echo $id;?>"
						       }).done(function(data) {
							       data = $.parseJSON(data);	                               
							       if(data.error == false){                                        
							       	alert('Hits has been reset successfully.');
							       }
							       else{
								       	alert('There is some problem in reseting hits counter.');
							       }
						       }).fail(function() {
							       alert('There is some error in sending ajax request.');
					       });
					}
				});
			});
		})(jQuery);

		<?php 
		$content = ob_get_contents();
		ob_end_clean();
		$doc->addScriptDeclaration($content);
		return true;	
	}

	public function onAfterRoute()
	{
		$app = JFactory::getApplication();	
		if($app->isSite()){
			return true;
		}

		$input 	= $app->input;	
		$plg 	= $input->get('plg', '');	
		$task 	= $input->get('task', '');
		$id 	= $input->get('id', '');
		if($plg !== 'plg_f90_reset_hits' || $task != 'reset_hits' || empty($id)){
			return true;
		}
	
		$sql = "UPDATE #__content SET `hits` = 0 WHERE `id` = ".$id;
		$db  = JFactory::getDbo();
		$db->setQuery($sql);

		if($db->query()){
			echo json_encode(array('error' => false));
		}
		else{
			echo json_encode(array('error' => true));
		}
		exit();
	}
}

