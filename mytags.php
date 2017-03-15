<?php
/**
*
* MyTags shows tags for article, taking them from keywords of article
*
* Copyright (C) 2008-2017 Denis Mokhin. All rights reserved. 
*
* Author is:
* Denis Mokhin < denis@mokhin-tech.ru >
* http://mokhin-tech.ru/mytags
*
* @license GNU GPL, see http://www.gnu.org/licenses/gpl-2.0.html
* 
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgContentMyTags extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}	
	
	/**
	* @since	1.6
	*/
	public function onContentAfterDisplay($context, &$row, &$params, $page=0)
	{
		if(empty($row->id))
			return "";
		if ($context == 'com_finder.indexer') {
			return "";
		}
		$place = (int)$this->params->get('place', 0);		
		$id=$row->id;
		return ($place==1)?$this->_showTags($id):"";		
	}
	
	/**
	* @since	1.6
	*/
	public function onContentBeforeDisplay($context, &$row, &$params, $page=0)
	{
		if(empty($row->id))
			return "";
		if ($context == 'com_finder.indexer') {
			return "";
		}
		$place = (int)$this->params->get('place', 0);		
		$id=$row->id;
		return ($place==0)?$this->_showTags($id):"";
	}
	
	/**
	 * Show Tags
	 * @param		object	$id	The id of material, wich tags should expand and show
	 */
	private function _showTags($id)
	{
		$add_nofollow =	(int)$this->params->get('add_nofollow',0);
		$limit = 		(int)$this->params->get('limit',0);
		$sep =				 $this->params->get('sep',' ');
		$plg_class_sfx =	 $this->params->get('plg_class_sfx','');
		$link_class_sfx =	 $this->params->get('link_class_sfx','');
		$is_end_space =	(int)$this->params->get('add_space_after_sep',0);
		$com2use = 		(int)$this->params->get('using_component',0);				
		
		$end_space_symb=($is_end_space==1)?' ':'';
		
		$db = JFactory::getDbo();
		$result	= null;
		$query=$db->getQuery(true);
		$query->select('metakey');
		$query->from('#__content');
		$query->where('id='.$id);		
		$db->setQuery($query);
		$result = $db->loadObjectList();	

		$html="";

		foreach($result as $tags)
		{
			$tag_r=preg_split("/\s*,+\s*/", $tags->metakey,-1,PREG_SPLIT_NO_EMPTY);
			if(count($tag_r)==0)
				continue;
			$html.= "<div class=\"plg_my_tags".$plg_class_sfx."\">" . JText::_( 'PLG_CONTENT_MYTAGS_TAGS'  );
			$i=1;
			if($add_nofollow==1)
				$html.="<noindex>";
		
			foreach($tag_r as $tag)
			{
				if($limit && $i > $limit)
					break;
				if($i>1)
					$html.=$sep.$end_space_symb;

				if($com2use==0)			
					$url=JRoute::_("index.php?option=com_search&searchword=".$tag."&searchphrase=exact&ordering=newest");								
				else
					$url=JRoute::_("index.php?option=com_finder&q=".$tag);
                
                $html.="<a href=\"".$url."\" title=\"".JText::_( 'PLG_CONTENT_MYTAGS_TAG'  ).": ".$tag."\"";					
				if($add_nofollow==1)				
					$html.=" rel=\"nofollow\"";
                if($link_class_sfx)
                    $html.=" class=\"".$link_class_sfx."\"";
				$html.=">".$tag."</a>";					
				$i++;
			}	
		
			if($add_nofollow==1)
				$html.="</noindex>";
		
			$html.= "</div>";	
			
			return $html;					
		}
	}
}