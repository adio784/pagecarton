<?php

/**
 * PageCarton
 *
 * LICENSE
 *
 * @category   PageCarton
 * @package    Ayoola_Page_Editor_Layout
 * @copyright  Copyright (c) 2011-2016 PageCarton (http://www.pagecarton.com)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Layout.php 10-26-2011 9.13pm ayoola $
 */

/**
 * @see Ayoola_Page_Editor_Abstract
 */
 
require_once 'Ayoola/Page/Editor/Abstract.php';


/**
 * @category   PageCarton
 * @package    Ayoola_Page_Editor_Layout
 * @copyright  Copyright (c) 2011-2016 PageCarton (http://www.pagecarton.com)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

class Ayoola_Page_Editor_Text extends Ayoola_Page_Editor_Abstract
{
 	
    /**
     * 
     * 
     * @var string 
     */
	protected static $_objectTitle = 'HTML Content'; 
	
    /**
     * For editable div in layout editor 
     * REMOVED BECAUSE IT CONFLICTS WITH THE EDITOR
     * 
     * @var string
     */
//	protected static $_editableTitle = "Open HTML editor";  

    /**
     * The View Parameter From Layout Editor
     *
     * @var string
     */
	protected $_viewParameter;

    /**
     * 
     *
     * @var array
     */
	protected $_markupTemplateObjects;
	
    /**
     * 
     * 
     * @var array
     */
	protected static $_widgetOptions = array( 
		'preserve_content' => 'Disable WYSIWYG',
		'embed_widgets' => 'Embed Widgets',
	);
	
	
    /**	
     *
     * @var boolean
     */
	public static $openViewParametersByDefault = true;
	
    /**
     * This method
     *
     * @param void
     * @return array
     */
    public function getMarkupTemplateObjects()
    {
		return $this->_markupTemplateObjects;
	}
	
	/**
	* This method
	*
	* @param 
	* @return 
	*/
	public static function fixUrlPrefix( $content, $prefixBefore = '', $prefixNow = '' )
	{
		if( $prefixBefore !== $prefixNow || $prefixNow )
		{
			$search = array( '"' . $prefixBefore, "'" . $prefixBefore, "url(" . $prefixBefore, '"' . $prefixNow, "'" . $prefixNow, "url(" . $prefixNow, );

			//	fix issue of $prefixBefore = /test and $prefixNow = /test/store
			if( stripos( $prefixBefore ? : '', $prefixNow ? : '' ) === 0 )
			{
				$search = array( '"' . $prefixBefore, "'" . $prefixBefore, "url(" . $prefixBefore, '"' . $prefixNow, "'" . $prefixNow, "url(" . $prefixNow, );
			}
			elseif( stripos( $prefixNow ? : '', $prefixBefore ? : '' ) === 0 )
			{
				$search = array( '"' . $prefixNow, "'" . $prefixNow, "url(" . $prefixNow, '"' . $prefixBefore, "'" . $prefixBefore, "url(" . $prefixBefore, );
			}
		//	var_export( $prefixNow );
		//	var_export( $prefixBefore );
			$replace = array( '"', "'", "url(", '"', "'", "url(", );
			$content = str_ireplace( $search, $replace, $content );
			$search = array( '"/', "'/", "url(/", $prefixNow . '//' );
			$replace = array( '"' . $prefixNow . '/', "'". $prefixNow . '/', "url(". $prefixNow . '/', '//' );
			$content = str_ireplace( $search, $replace, $content );
		}
		return $content;
	}

	/**
     * This method
     *
     * @param 
     * @return 
     */
    public static function addDomainToAbsoluteLinks( $content )
    {
		$rootUrl = Ayoola_Page::getRootUrl();
		$search = array( '"/', "'/", "url(/", $rootUrl . '//' );
		$replace = array( '"' . $rootUrl . '/', "'". $rootUrl . '/', "url(". $rootUrl . '/', '//' );
		$content = str_ireplace( $search, $replace, $content );
		return $content;
	}

    /**
     * Do a one time parameter filter within widgets
     *
     */
	public static function filterParameters( & $parameters )
    {
		$content = $parameters['codes'] ? : ( $parameters['editable'] ? : $parameters['view'] );
		if( ( @in_array( 'preserve_content', $parameters['widget_options'] ) || @in_array( 'preserve_content', $parameters['text_widget_options'] ) ) && $parameters['preserved_content'] )
		{
			@$content = $parameters['codes'] ? : $parameters['preserved_content'];
		}
        preg_match_all( '|\{-(.*)-\}|', $content, $matches );
        #   '{-Lorem Ipsum dolor-}'
      //  self::v( $content );
      //  self::v( $matches );
        $previousData = Ayoola_Page_Layout_ReplaceText::getUpdates();

        foreach( $matches[0] as $count => $each )
        {
        //    self::v( $each );
            $previousData['dummy_title'][] = 'Replaceable Text ' . self::$_counter . $count;
            $previousData['dummy_search'][] = $each;
            $previousData['dummy_replace'][] = trim( $each, '{-}' );
            Ayoola_Page_Layout_ReplaceText::saveTexts( $previousData );
        }

        //  to be executed within the widget class
        foreach( [ 'codes', 'editable', 'preserved_content' ] as $each  )
        {
       //   if( @$parameters['url_prefix'] !== Ayoola_Application::getUrlPrefix() ||  Ayoola_Application::getUrlPrefix() )
            {		
            //    @$parameters[$each] = self::fixUrlPrefix( $parameters[$each], @$parameters['url_prefix'], Ayoola_Application::getUrlPrefix() );
            }
        }
    }
	
    /**
     * This method
     *
     * @param 
     * @return 
     */
    public function init()
    {
		//	codes first because it wont be there if they didnt opt to enter codes
	//	var_export( $this->_parameter );  
		$content = $this->getParameter( 'codes' ) ? : ( $this->getParameter( 'editable' ) ? : $this->getParameter( 'view' ) );
		if( ( @in_array( 'preserve_content', $this->getParameter( 'widget_options' ) ) || @in_array( 'preserve_content', $this->getParameter( 'text_widget_options' ) ) ) && $this->getParameter( 'preserved_content' ) )
		{
			@$content = $this->getParameter( 'codes' ) ? : $this->getParameter( 'preserved_content' );
		}
		$textUpdatesSettings = Ayoola_Page_Layout_ReplaceText::getUpdates( true );
		if( empty( $textUpdatesSettings['dummy_search'] ) )
		{
			$textUpdatesSettings = Ayoola_Page_Layout_ReplaceText::getDefaultTexts();
		}
	//	self::v( $textUpdatesSettings );  
		$content = str_replace( $textUpdatesSettings['dummy_search'], $textUpdatesSettings['dummy_replace'], $content );
	//	self::v( $content );  
	//	exit();          
	//	self::v( $textUpdatesSettings );  
	//	exit();          
		$content = self::__( $content );
	//	var_export( $this->getParameter( 'markup_template_object_name' ) );
		if( $this->getParameter( 'markup_template_object_name' ) )
		{
			$classes = (array) $this->getParameter( 'markup_template_object_name' );    
			foreach( $classes as $counter => $each )
			{	
				if( ! Ayoola_Loader::loadClass( $each ) )
				{
					continue;
				}

				//	Removing time() from namespace because it doesn't allow the post to cache
				//	Use whole content or specified part
				$i = 0;
		//				var_export( $each );
		//				var_export( $content );
				$start = '<!--{{{@' . $counter . '(' . $each . ')-->';
				$end = '<!--(' . $each . ')@' . $counter . '}}}-->';
				if( stripos( $content, $start ) === false || stripos( $content, $end ) === false )
				{
					$start = '{{{@' . $counter . '(' . $each . ')';
					$end = '(' . $each . ')@' . $counter . '}}}';
				}
				if( stripos( $content, $start ) === false || stripos( $content, $end ) === false )
				{
					$start = '{{{@(' . $each . ')';
					$end = '(' . $each . ')@}}}';
					if( stripos( $content, $start ) === false || stripos( $content, $end ) === false )
					{
						$start = '{{{@' . $each . '';
						$end = '' . $each . '@}}}';
						if( stripos( $content, $start ) === false || stripos( $content, $end ) === false )
						{
							$parameters = array( 
												'markup_template' => $content, 
												'markup_template_namespace' => 'x1234', 
												'parameter_suffix' => '[' . $counter . ']', 
										//		'editable' => $each 
												) 
												+ $this->getParameter();  
							self::unsetParametersThatMayBeDuplicated( $parameters );
							$class = new $each( $parameters );
							$content = $class->view();
							$this->_markupTemplateObjects[] = $class;
						}
					}
				}
			//	var_export( $each );
			//	var_export( $start );
			//	var_export( $end );
				while( stripos( $content, $start ) !== false && stripos( $content, $end ) !== false && ++$i < 5 )
				{
					$started = stripos( $content, $start );
					$length = ( stripos( $content, $end ) + strlen( $end ) )  - $started;
					$partTemplate = substr( $content, $started, $length );
			//		var_export( $partTemplate );
					$searchY = array();
					$replaceY = array();
					$searchY[] = $start;
					$replaceY[] = '';
					$searchY[] = $end;
					$replaceY[] = '';
					$partTemplateToUse = str_ireplace( $searchY, $replaceY, $partTemplate );
					$parameters = array( 
						'markup_template' => $partTemplateToUse, 
						'markup_template_namespace' => 'x1234', 
						'parameter_suffix' => '[' . $counter . ']', 
					//	'editable' => $each 
						) 
						+ $this->getParameter();  
					
					self::unsetParametersThatMayBeDuplicated( $parameters );
					$class = new $each( $parameters );
					$returnedContent = $class->view();
					$this->_markupTemplateObjects[] = $class;
					$returnedContent = str_ireplace( $searchY, $replaceY, $returnedContent );
					
					$searchC = array();
					$replaceC = array();
					$searchC[] = $partTemplate;
					$replaceC[] = $returnedContent;
					$content = str_ireplace( $searchC, $replaceC, $content );
				}  
			}

			$content .= '<div style="clear:both;"></div>';  
			$content .= '<div style="clear:both;"></div>';  
		}
		if( $this->getParameter( 'page_title' ) || $this->getParameter( 'page_description' )  )
		{
			$metaData = strip_tags( $content );
		//	var_export( Ayoola_Page::getCurrentPageInfo( 'title' ) );
			if( $this->getParameter( 'page_title' ) )
			{
				$pageInfo['title'] = $metaData;
			}
			if( $this->getParameter( 'page_description' ) )
			{
				$pageInfo['description'] = $metaData;
			}
			Ayoola_Page::setCurrentPageInfo( $pageInfo );
		}

		//	Refreshes the url prefix just in case we have imported new site
//	var_export( $this->getParameter( 'url_prefix' ) );
//	var_export( Ayoola_Application::getUrlPrefix() );
	//	var_export( strpos( $content, '//' ) );
	//	var_export( $content );  
//		var_export( $this->getParameter() );  
		if( $this->getParameter( 'url_prefix' ) !== Ayoola_Application::getUrlPrefix() ||  Ayoola_Application::getUrlPrefix() )
		{		
			$content = self::fixUrlPrefix( $content, $this->getParameter( 'url_prefix' ), Ayoola_Application::getUrlPrefix() );
		}
		
		$this->setParameter( array( 'editable' => $content ) );
		$html = $this->getParameter( 'editable' ) . $this->getParameter( 'raw_html' );
		if( $this->getParameter( 'nl2br' ) )
		{
			$html = nl2br( $html );  
		}
	//	$this->
		$this->_parameter['no_view_content_wrap'] = true;
		$this->setViewContent( $html );
		if( $this->getParameter( 'javascript_code' ) )
		{

			Application_Javascript::addCode
			(
				$this->getParameter( 'javascript_code' )
			);
		}					

	//	var_export( $this->_parameter );
     //   return $content . $this->getParameter( 'raw_html' );
    } 

    /**
	 * Returns text for the "interior" of the Layout Editor
	 * The default is to display view and option parameters.
	 * 		
     * @param array Object Info
     * @return string HTML
     */
    public static function getHTMLForLayoutEditorAdvancedSettings( & $object )
	{

		$html = null;
		if( ( @in_array( 'embed_widgets', $object['widget_options'] ) || @in_array( 'embed_widgets', $object['text_widget_options'] ) ) || @$object['markup_template_object_name'] )
		{
			$object['markup_template_object_name'] = (array) $object['markup_template_object_name'];
			$widgets = Ayoola_Object_Embed::getWidgets();
			foreach( $object['markup_template_object_name'] as $each )
			{
				if( $each && ! array_key_exists( $each, $widgets ) )
				{ 
					$widgets[$each] = $each;   
				}
			}
			$i = 0;   
			do
			{
				$fieldset = new Ayoola_Form_Element; 
				$fieldset->hashElementName = false;
				$fieldset->container = 'span';
				$fieldset->addElement( array( 'name' => 'markup_template_object_name[]', 'label' => 'Widget  <span name="embed_widget_counter" class="embed_widget_counter">' . ( $i ) . '</span>', 'style' => '', 'type' => 'Select', 'onchange' => 'if( this.value == \'__custom\' ){ var a = prompt( \'' . self::__( 'Custom Parameter Name' ) . '\', \'\' ); if( ! a ){ this.value = \'\'; return false; } var option = document.createElement( \'option\' ); option.text = a; option.value = a; this.add( option ); this.value = a;  }', 'value' => @$object['markup_template_object_name'][$i] ), array( '' => 'Select Widget' ) + $widgets + array( '__custom' => 'Custom Widget' ) );  
				if( $object['markup_template_object_name'][$i] )
				{
					$fieldset->allowDuplication = true;  
					$fieldset->duplicationData = array( 'add' => '+ Embed New Widget', 'remove' => '- Remove Above Widget', 'counter' => 'embed_widget_counter', );
				}
				$fieldset->placeholderInPlaceOfLabel = true;
				$fieldset->addLegend( '' );   			   			
				$html .= $fieldset->view(); 

				$class = @$object['markup_template_object_name'][$i];
				$content = null;
				$resultsVar = null;

				if( ! empty( $class ) && Ayoola_Loader::loadClass( $class ) )
				{
					$filter = new Ayoola_Filter_ClassToFilename();
					$classFile = $filter->filter( $class );
					$classFile = Ayoola_Loader::getFullPath( $classFile );
	
					$content = file_get_contents( $classFile ) ;
					preg_match_all( "/\['([a-z_-]*)'\]/", $content, $resultsVar );
					$resultsVar = ( is_array( $resultsVar[1] ) ? $resultsVar[1] : array() );
				}
				if( $resultsVar )
				{
					$resultsVar = array_unique( $resultsVar );
					sort( $resultsVar );
					$data = trim( str_replace( '{{{}}},', '', '{{{' . implode( '}}}, {{{', $resultsVar ) . '}}}' ), ' ' );
					
					$html .= '<div>';  
					$html .= '<textarea style="font-size:12px;" readonly rows="5" style="height:auto;" ondblclick="ayoola.div.autoExpand( this );">';  
					$html .= '<!-- How to embed ' . $class . ' -->
<!--{{{@' . $i . '(' . $class . ')-->
<p>' . self::__( 'Insert HTML content here. Use varables like' ) . ' {{{' . ( $resultsVar[0] ? : $resultsVar[1] ) . '}}} here.</p>
<!--(' . $class . ')@' . $i . '}}}-->
<!-- Place this code in code view -->';  
								
					$html .= '</textarea>'; 

					$html .= '<textarea  style="font-size:12px;" readonly ondblclick="ayoola.div.autoExpand( this );">';  
					$html .= '' . $class . ' ' . self::__( 'variables to use in content' ) . ': ' . $data . '
									';  
								
					$html .= '</textarea>';  
					$html .= '</div>';  
				}
				$i++;

			}
			while( isset( $object['markup_template_object_name'][$i] ) );
		}
		
		return $html;
	}

    /**
	 * Returns text for the "interior" of the Layout Editor
	 * The default is to display view and option parameters.
	 * 		
     * @param array Object Info
     * @return string HTML
     */
     public static function getHTMLForLayoutEditor( & $object )
	{
		if( empty( $object['widget_options'] ) &&  ! empty( $object['text_widget_options'] ) )
		{
			$object['widget_options'] = $object['text_widget_options'];
		}

		Application_Javascript::addFile( '' . Ayoola_Application::getUrlPrefix() . '/js/objects/ckeditor/ckeditor.js?x' );    
		Application_Javascript::addCode
										( 	'
												CKEDITOR.plugins.addExternal( "uploadimage", "' . Ayoola_Application::getUrlPrefix() . '/js/objects/ckeditor/plugins/uploadimage/plugin.js", "" );
												CKEDITOR.plugins.addExternal( "confighelper", "' . Ayoola_Application::getUrlPrefix() . '/js/objects/ckeditor/plugins/confighelper/plugin.js", "" );
												CKEDITOR.config.extraPlugins = "confighelper,uploadimage";
												CKEDITOR.config.removePlugins = "maximize,resize,elementspath";
												CKEDITOR.config.allowedContent  = true;
												CKEDITOR.dtd.$removeEmpty["i"] = false;
												CKEDITOR.dtd.$removeEmpty["a"] = false;
												CKEDITOR.config.filebrowserUploadUrl = "' . Ayoola_Application::getUrlPrefix() . '/tools/classplayer/get/object_name/Ayoola_Doc_Upload_Ajax/?";   
												
												//	We need to disable auto-inline to correct some content that manipulate after load
												CKEDITOR.disableAutoInline = true;
												CKEDITOR.config.toolbar = 
															[
														//		{ items: [ "Source", "-", "Save", "NewPage", "Preview", "Print", "-", "Templates" ] },
																{ name: "basicstyles", groups: [ "basicstyles", "cleanup" ], items: [ "Bold", "Italic", "Underline", "Strike", "-", "RemoveFormat" ] },
//																{ name: "basicstyles", groups: [ "basicstyles", "cleanup" ], items: [ "Bold", "Italic", "Underline", "Strike", "Subscript", "Superscript", "-", "RemoveFormat" ] },
																{ name: "paragraph", groups: [ "list", "indent", "blocks", "align" ], items: [ "NumberedList", "BulletedList", "-", "Outdent", "Indent", "-", "Blockquote", "-", "CreateDiv", "-", "JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock", "-" ] },
																{ name: "links", items: [ "Link", "Unlink", "Anchor" ] },
																{ name: "styles", items: [ "Format", "Font", "FontSize" ] },
																{ name: "colors", items: [ "TextColor", "BGColor" ] },
																{ name: "insert", items: [ "Image", "Table", "HorizontalRule", "SpecialChar", "Iframe" ] }
																
															];
												function replaceDiv( div )  
												{
													//	reset
													div.onclick = "";
													div.setAttribute( \'contentEditable\', \'true\' ); 

													if ( ayoola.div.wysiwygEditor )
													{
													//	ayoola.div.wysiwygEditor.destroy();
													}
													//	destroy all instances of ckeditor everytime state changes.
													for( name in CKEDITOR.instances )
													{
													//	CKEDITOR.instances[name].destroy();
													}
													ayoola.div.wysiwygEditor = CKEDITOR.inline
													( 
														div,
														{

														}
													);  
													
												}
											' 
										);    
		$html = null;
	//	$optionsName = 'text_widget_options';
		if( ( @in_array( 'preserve_content', $object['widget_options'] ) || @in_array( 'preserve_content', $object['text_widget_options'] ) ) )
		{
			@$object['editable'] = $object['preserved_content'] ? : ( $object['codes'] ? : $object['editable'] );
		}
		@$object['view'] = $object['view'] ? : $object['view_parameters'];    
		@$object['option'] = $object['option'] ? : $object['view_option'];
		if( @$object['url_prefix'] !== Ayoola_Application::getUrlPrefix() ||  Ayoola_Application::getUrlPrefix() )
	//	if( @$object['url_prefix'] !== Ayoola_Application::getUrlPrefix() && strpos( $content, '//' ) === false )
		{
			$search = array( 
								'"' . @$object['url_prefix'], 
								"'" . @$object['url_prefix'], 
								'"' . Ayoola_Application::getUrlPrefix(), 
								"'" . Ayoola_Application::getUrlPrefix(), 
								"url(" . @$object['url_prefix'], 
								"url(" . Ayoola_Application::getUrlPrefix(), 
								);
			$replace = array( 
								'"', 
								"'", 
								'"', 
								"'", 
								"url(", 
								"url(", 
								);
			@$object['codes'] ? $object['codes'] = str_ireplace( $search, $replace, @$object['codes'] ) : null;
			@$object['editable'] ? $object['editable'] = str_ireplace( $search, $replace, @$object['editable'] ) : null;
			$search = array( '"/', "'/", "url(", );
			$replace = array( 
								'"' . Ayoola_Application::getUrlPrefix() . '/', 
								"'" . Ayoola_Application::getUrlPrefix() . '/', 
								"url(" . Ayoola_Application::getUrlPrefix(), 
								);
			@$object['codes'] ? $object['codes'] = str_ireplace( $search, $replace, $object['codes'] ): null;
			@$object['editable'] ? $object['editable'] = str_ireplace( $search, $replace, $object['editable'] ): null;
	//		$replace = Ayoola_Application::getUrlPrefix();
		}

		if( ! @$object['codes'] )
		{

			if( ( @in_array( 'preserve_content', $object['widget_options'] ) || @in_array( 'preserve_content', $object['text_widget_options'] ) ) )
			{
				$html .= '<div data-pc-preserve-content="1" class="preserved_content_view pc_html_editor" data-parameter_name="editable" title="' . self::__( 'The content has been locked from editing...' ) . '">';
			}
			else
			{
				$html .= '<div style=" cursor: text;" data-parameter_name="editable" title="' . self::__( 'You may click to edit the content here...' ) . '" contentEditable="true" class="ckeditor pc_html_editor" onDblClick="replaceDiv( this );">';
			}
			
			
			$html .= ( isset( $object['editable'] ) ? $object['editable'] : '
			
			<h3>' . self::__( 'Lorem Ipsum dolor' ) . '</h3>
			<p>' . self::__( 'Vivamus sit amet dolor sit amet nunc maximus finibus. Donec vel ornare leo, eget gravida orci. Etiam vitae rutrum nisi. Mauris auctor velit et ultricies mollis. Donec in mattis lectus. In hac habitasse platea dictumst. Sed ultricies magna ut ligula fringilla facilisis. Ut sodales erat ut libero rhoncus hendrerit. Vivamus nunc magna, finibus vel velit in, tempus venenatis dolor. Aenean a leo non tellus semper ultricies eget quis enim.' ) . '</p>
			' ) .
			
			'</div>';  
		}
		elseif( @$object['codes']  )
		{
			$html .= '<textarea class="pc_page_object_specific_item" data-parameter_name="codes" style="' . $hiddenStyle . 'width:100%; background-color:inherit; color:inherit;" title="' . self::__( 'You may click to edit the content here...' ) . '" >' . htmlspecialchars( @$object['codes'] ? : $object['editable'] ) . '</textarea>';     
		}
		$html .= '<textarea class="" data-parameter_name="preserved_content" style="display:none;" title="" >' . htmlspecialchars( @$object['editable'] ) . '</textarea>';     

		//	Use this to clean the URL prefix from the codes
		$html .= '<input data-parameter_name="url_prefix" type="hidden" value="' . Ayoola_Application::getUrlPrefix() . '" >';  
		$html .= '<div style="clear:both;"></div>';  
		if( ! ( @in_array( 'preserve_content', $object['widget_options'] ) || @in_array( 'preserve_content', $object['text_widget_options'] ) ) )
		{

			Application_Javascript::addCode
			(
				'
					var divToCodeEditor = function( trigger )
					{
						// create textarea
						var e = trigger.parentNode.parentNode.getElementsByTagName( \'textarea\'); 
					//	alert( e );
					//	alert( trigger.parentNode.parentNode.innerHTML );
					//	if( e.length )
						{
							var c = false;
							for( var b = 0; b < e.length; b++ )
							{ 
					//			alert( e[b].getAttribute( \'data-parameter_name\' ) );
					//			alert( e[b].style.display );
								if( e[b].name == \'' . __CLASS__ . '_code_editor\' )
								{
									var c = e[b];  
								}
								else if( e[b].getAttribute( \'data-parameter_name\' ) == \'codes\' )
								{
									//	saving as codes makes us not have the ckeditor again
									var f = document.createElement( \'div\' ); 
									var fx = document.createElement( \'div\' ); 
									fx.appendChild( f );
									f.className = \'ckeditor\'; 
								//	alert( f.outerHTML );
								//	alert( f.innerHTML );
									if( f.outerHTML )
									{
										f.outerHTML = \'<div data-parameter_name="editable" title="' . self::__( 'You may click to edit the content here...' ) . '" contentEditable="true" class="ckeditor"  onClick="replaceDiv( this );" onDblClick="replaceDiv( this );">\' + e[b].value +  \'</div>\';  
									}
							//		f. = 5; 
									f.setAttribute( \'onClick\', \'replaceDiv( this );\' ); 
									f.setAttribute( \'contentEditable\', \'true\' ); 
									
									//	new ckeditor 
									e[b].parentNode.insertBefore( f, e[b] ); 
									var c = e[b];
								}
								else if( e[b].getAttribute( \'data-parameter_name\' ) == \'preserved_content\' )
								{
									//	saving as codes makes us not have the ckeditor again
									var xx = e[b];
								}
							}
						}
						var a = trigger.parentNode.parentNode.getElementsByClassName( \'ckeditor\'); 
				//		alert(a.length);
				//		alert(c);
						if( ! c && ! a.length )
						{
							//	preserved content era
							var xy = trigger.parentNode.parentNode.getElementsByClassName( \'preserved_content_view\')[0];
						//	alert(xy);
						//	alert(xy.innerHTML);
							switch( xx.style.display )
							{
								case "none":
									xy.style.display = "none";
									xx.style.width = "100%";
									xx.style.display = "block";
									xx.focus();
								break;
								default:
									xy.innerHTML = xx.value;
									xy.style.display = "";
									xx.style.display = "none";
								break;

							}
							return true;
						}
						if( ! c )
						{

								//	saving this is causing conflicts, so new textarea for each request
								var c = document.createElement( \'textarea\' ); 
								c.name = \'' . __CLASS__ . '_code_editor\'; 
								c.rows = 5; 
								c.setAttribute( \'style\', \'display:block; width:100%;\' ); 
						}
						for( var b = 0; b < a.length; b++ )
						{  
							if( trigger.innerHTML == \'WYSIWYG\' )
							{ 
								a[b].innerHTML = c.value ? c.value : a[b].innerHTML;  
								
								a[b].style.display = \'block\'; 
								c.style.display = \'none\'; 
								trigger.innerHTML = \'' . self::__( 'Code View' ) . '\'; 
								c.setAttribute( \'data-parameter_name\', \'\' ); 
								a[b].setAttribute( \'data-parameter_name\', \'editable\' ); 
								c.parentNode.removeChild( c ); 
								
							} 
							else
							{ 
								if( CKEDITOR )
								for( name in CKEDITOR.instances )
								{
									//	Destroy ckeditor so it could clean up the  code for Code Editor
									CKEDITOR.instances[name].destroy();
								}
								a[b].parentNode.insertBefore( c, a[b] ); 
								a[b].style.display = \'none\';  
								trigger.innerHTML = \'' . self::__( 'WYSIWYG' ) . '\'; 
								c.value = a[b].innerHTML; 
								c.setAttribute( \'data-parameter_name\', \'codes\' ); 
								a[b].setAttribute( \'data-parameter_name\', \'\' ); 
								c.focus(); 
							} 
						//	trigger.style.display = \'\'; 
						}
					}
				'
			);
		}					
		return $html;
	}

    /**
     * Returns an array of other classes to get parameter keys from
     *
     * @param void
     * @return array
     */
    protected static function getParameterKeysFromTheseOtherClasses( & $parameters )
    {
	//	var_export( $parameters['editable'] );
		$classes = array();
		if( @$parameters['markup_template_object_name'] )
		{
			$classes = (array) $parameters['markup_template_object_name'];
		}
		return $classes;
	}
 
	
    /**
     * This method
     *
     * @param 
     * @return 
     */
    public static function getStatusBarLinks( $object )
    {	
		$optionsName = 'text_widget_options';
	//	if( ! @in_array( 'preserve_content', $object[$optionsName] ) )
		{
			return '<a class="title_button" title="' . self::__( 'Switch the editing mode' ) . '" name="" href="javascript:;" onclick="divToCodeEditor( this );return true;">' . ( isset( $object['codes'] ) ? '' . self::__( 'WYSIWYG' ) . '' : '' . self::__( 'Code View' ) . '' ) . '</a>';
		}
	}
	
	// END OF CLASS
}
