<?php
/*
Plugin Name: Embed Object
Plugin URI: http://www.idealmind.com.br/wordpress/embed-object-wordpress-plugin-youtube-flash/
Description: Este plugin permite que você insira objetos flash em seu blog, inclusive passando parâmetros por flashvars.<br />This plugin let's you embed flash objects in your blog and use flashvars.
Version: 1.0
Author: Wellington Ribeiro
Author URI: http://www.idealmind.com.br

1.0   - Initial release
*/
Class embedObject
{
	private static $debug;
	
	public static function justDoIt( $content, $debug = false )
	{
		self::$debug = $debug;
		$pattern = "/\[embed:([^\]]+)\]/";
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
			return "[embed: $attr]";
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
	if( ( is_single() || is_home() || is_page() ) && !is_feed() )
	{
		if( isset( $_GET['debug'] ) )
		{
			$debug = true;
		}
		$content = embedObject::justDoIt( $content, $debug );
	}
	return $content;
}

$teste = false;

if(!$teste)
{
	add_filter ('the_content', 'embedObject');
}
else 
{
	$var = '
		Isso é um teste de objetos<br />
		[embed: src="http://www.youtube.com/v/4f0Mepuh3vM&hl=pt_BR&fs=1&" wmode="transparent" allowscriptaccess="always" allowfullscreen="true" FlashVars="foo=Hello%20Worldgraph=first+line%0Dsecond+line" width="560" height="340"]
		<br />e abaixo <br /> outro objeto com poucos atributos<br />
		[embed: src="http://www.youtube.com/v/N6ydZdJWEfw&hl=pt_BR&fs=1&" width="560" height="340" visualizar="true"]
		<br />Fim
		';
	if( isset( $_GET['debug'] ) )
	{
		$debug = true;
	}
	echo embedObject::justDoIt( $var, $debug );
}

?>