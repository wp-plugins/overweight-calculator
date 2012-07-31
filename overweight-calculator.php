<?php
/*
Plugin Name: Overweight Calculator
Plugin URI: http://calendarscripts.info/overweight-calculator-wordpress-plugin.html
Description:This plugin displays functional overweight calculator / BMI calculator. It can be used to check the user's Body Mass Index and suggest the recommended weight range. <strong>Just enter [overweight-calculator] in any post or page</strong> and the calculator will be displayed. <strong>See also <a href="http://calendarscripts.info/weight-loss-calculator.html">this one</a></strong> - it might be interesting for sharing with your readers.  
Author: Bobby Handzhiev, prasunsen
Version: 1.2
Author URI: http://pimteam.net/
*/ 

/*  This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


function overweightcalculator_add_page()
{
	add_submenu_page('plugins.php', 'Overweight Calculator Configuration', 'Overweight Calculator Configuration', 8, __FILE__, 'overweightcalculator_options');
}

// firstimer_options() displays the page content for the FirstTimer Options submenu
function overweightcalculator_options() 
{
    // Read in existing option value from database
    $ocalc_table = stripslashes( get_option( 'ocalc_table' ) );
    
    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( $_POST[ 'ocalc_update' ] == 'Y' ) 
    {
        // Read their posted value
        $ocalc_table = $_POST[ 'ocalc_table' ];
        

        // Save the posted value in the database
        update_option( 'ocalc_table', $ocalc_table );
        
        // Put an options updated message on the screen
		?>
		<div class="updated"><p><strong><?php _e('Options saved.', 'ocalc_domain' ); ?></strong></p></div>
		<?php		
	 }
		
		 // Now display the options editing screen
		    echo '<div class="wrap">';		
		    // header
		    echo "<h2>" . __( 'Overweigth Calculator Options', 'ocalc_domain' ) . "</h2>";		
		    // options form		    
		    ?>
		<div>
		<form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="ocalc_update" value="Y">
		
		<p><?php _e("CSS class definition for the calculator table:", 'ocalc_domain' ); ?> 
		<textarea name="ocalc_table" rows='5' cols='70'><?php echo stripslashes ($ocalc_table); ?></textarea>
		</p><hr />
		
		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Update Options', 'ocalc_domain' ) ?>" />
		</p>
		
		</form>
		</div>
		<?php
}

// This just echoes the text
function overweight_calculator($content) 
{
	if(!strstr($content,"[overweight-calculator]")) return $content;
	
	$firsttext="Your Body Mass Index (BMI) is %%BMI%%. This means your weight is within the %%BMIMSG%% range.";

	$normaltext="You seem to keep your weight normal. Well done!";
	
	$lowertext="Your current BMI is lower than the recommended range of <strong>18.5</strong> to <strong>24.9</strong>. <br>To be within the right range for your height, you should weigh between <strong>%%LOWERLIMIT%% lbs</strong> / <strong>%%LOWERLIMITKG%% kg</strong> and <strong>%%UPPERLIMIT%% lbs</strong> / <strong>%%UPPERLIMITKG%% kg</strong>";
	
	$uppertext="Your current BMI is greater than the recommended range of <strong>18.5</strong> to <strong>24.9</strong>. <br>To be within the right range for your height, you should weigh between <strong>%%LOWERLIMIT%% lbs</strong> / <strong>%%LOWERLIMITKG%% kg</strong> and <strong>%%UPPERLIMIT%% lbs</strong> / <strong>%%UPPERLIMITKG%% kg</strong>";
	
	//construct the calculator page	
	$ovcalc="<style type=\"text/css\">
	.ocalc_table
	{
		".get_option('ocalc_table')."
	}
	</style>\n\n";
	
	if(!empty($_POST['calculator_ok']))
	{
		$height=$_POST['height'];
		$bmi=($_POST['weight']*703) / ($height*$height);
		$bmi=number_format($bmi,1,".","");
		
		// prepare message for the user
		if($bmi<=18.5)
		{
			$bmimsg="Underweight";
		}
		
		if($bmi>18.5 and $bmi<=24.9)
		{
			$bmimsg="Normal";	
		}
		
		if($bmi>=25 and $bmi<=29.9)
		{
			$bmimsg="Overweight";			
		}
		
		if($bmi>=30)
		{
			$bmimsg="Obese";		
		}
		
		// what is the weight range?
		if($bmimsg!='Normal')
		{
			$lowerlimit=number_format(( 18.5 * ($height*$height) ) / 703);
			$lowerlimitkg=number_format($lowerlimit*0.453,1,".","");
			
			$upperlimit=number_format(( 24.9 * ($height*$height) ) / 703);
			$upperlimitkg=number_format($upperlimit*0.453,1,".","");
		}
		
		//prepare texts
		$firsttext=str_replace("%%BMI%%",$bmi,$firsttext);
		$firsttext=str_replace("%%BMIMSG%%",$bmimsg,$firsttext);
		$lowertext=str_replace("%%LOWERLIMIT%%",$lowerlimit,$lowertext);
		$lowertext=str_replace("%%LOWERLIMITKG%%",$lowerlimitkg,$lowertext);
		$lowertext=str_replace("%%UPPERLIMIT%%",$upperlimit,$lowertext);
		$lowertext=str_replace("%%UPPERLIMITKG%%",$upperlimitkg,$lowertext);
		$uppertext=str_replace("%%LOWERLIMIT%%",$lowerlimit,$uppertext);
		$uppertext=str_replace("%%LOWERLIMITKG%%",$lowerlimitkg,$uppertext);
		$uppertext=str_replace("%%UPPERLIMIT%%",$upperlimit,$uppertext);
		$uppertext=str_replace("%%UPPERLIMITKG%%",$upperlimitkg,$uppertext);
			
		//the result is here
		$ovcalc.="<div class=\"ocalc_table\">\n
		<p>$firsttext</p>\n";
		
		switch($bmimsg)
	    {	
	       case 'Normal':
	     		// you can echo here if you want for normal weight people
	       break;
	       
	       case 'Underweight':       		
	       		$ovcalc.= $lowertext;
	       break;
	                                         	
	       default:
	       		$ovcalc.= $uppertext;
	        break;                              
	      }
	      
	      
	      $ovcalc.='<p align="center"><a href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'">Calculate again</a></p>
	      </div>';
	      
	}
	else
	{
		$ovcalc.="<form method=\"post\">
		<table class=\"ocalc_table\" align=\"center\">
		<tr><td class='ocalc_titlecell'>Your Weight:</td>
		<td><select name=\"weight\">";
	     
	     for($i=50;$i<=600;$i++)
	     {
		    $kg=number_format($i*0.453,1,".","");
			if($i==170) $selected="selected"; // default to some reasonable value
			else $selected="";
		    $ovcalc.="<option $selected value='$i'>$kg kg / $i lbs</option>";
	     }
	     
	     $ovcalc.="</select></td></tr>
		<tr><td class='ocalc_titlecell'>Your Height:</td>
		<td><select name=\"height\">";
		
	     for($i=50;$i<=88;$i++)
	     {
		    $feets=floor($i/12);
		    $in=$i%12;
		    $cm=number_format($i*2.54);
			if($i==70) $selected="selected"; // default to some reasonable value
			else $selected="";
		    $ovcalc.="<option $selected value='$i'>$cm cm / $feets ft $in in</option>";
	     }
	    
		$ovcalc.='</select></td></tr>
		<tr><td colspan="2" align="center">
		<input type="hidden" name="calculator_ok" value="ok">
		<input type="submit" value="Are You Overweight?">
		</td></tr>			
		</table>
		</form>';
	}
	
	$content=str_replace("[overweight-calculator]",$ovcalc,$content);
	return $content;
}

add_action('admin_menu','overweightcalculator_add_page');
add_filter('the_content', 'overweight_calculator');