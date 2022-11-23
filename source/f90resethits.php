<?php
/**
 * @package 	Plugin ResetHits for Joomla! 4.X
 * @version 	0.0.2
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
		if($app->isClient('site')){
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
			
		$major  = JVersion::MAJOR_VERSION . JVersion::MINOR_VERSION;
		
		if($major == '25'){
			$doc->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');
		}

		?>
		(function($){
			$(document).ready(function(){
				<?php if($major == '25'):?>
					$('<li id="toolbar-reset-hits" class="button"><a id="f90-reset-hits" class="toolbar" href="#"><span class="icon-32-purge"> </span>Reset Hits</a></li>')
					.insertAfter('#toolbar-cancel');
				<?php else: ?>
					$('<joomla-toolbar-button id="toolbar-reset-hits" class="btn-wrapper"><button id="f90-reset-hits" class="btn btn-small"><span class="icon-refresh"></span>Reset Hits</button></joomla-toolbar-button>')
					.insertAfter('#toolbar-cancel');
				<?php endif; ?>
				$('#f90-reset-hits').click(function(){
					if(confirm('Are you sure you want to reset hit counter of this article? This will set hits to 0.')){
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
		if($app->isClient('site')){
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

