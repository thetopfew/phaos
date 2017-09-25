<?php 

function displaysmileys($textarea) {
   $smileys = array (
      ":)" => "smile.gif",
      ";)" => "wink.gif",
      ":|" => "frown.gif",
      ":(" => "sad.gif",
      ":o" => "shock.gif",
      ":p" => "pfft.gif",
      "B)" => "cool.gif",
      ":D" => "grin.gif",
      ":@" => "angry.gif"
   );
   foreach($smileys as $key=>$smiley) $smiles = "<img class='button' src='images/smiley/$smiley' width='20px' onClick=\"insertText('$textarea', '$key');\">\n";
   return $smiles;
}

function parsesmileys($message) {
   $smiley = array(
      "/\:\)/si" => "<img src='images/smiley/smile.gif'>",
      "/\;\)/si" => "<img src='images/smiley/wink.gif'>",
      "/\:\(/si" => "<img src='images/smiley/sad.gif'>",
      "/\:\|/si" => "<img src='images/smiley/frown.gif'>",
      "/\:o/si" => "<img src='images/smiley/shock.gif'>",
      "/\:p/si" => "<img src='images/smiley/pfft.gif'>",
      "/b\)/si" => "<img src='images/smiley/cool.gif'>",
      "/\:d/si" => "<img src='images/smiley/grin.gif'>",
      "/\:@/si" => "<img src='images/smiley/angry.gif'>"
   );
   foreach($smiley as $key=>$smiley_img) $message = preg_replace($key, $smiley_img, $message);
   return $message;
}

function parseubb($message) {
	$ubbs1[0] = '#\[b\](.*?)\[/b\]#si';
	$ubbs2[0] = '<b>\1</b>';
	$ubbs1[1] = '#\[i\](.*?)\[/i\]#si';
	$ubbs2[1] = '<i>\1</i>';
	$ubbs1[2] = '#\[u\](.*?)\[/u\]#si';
	$ubbs2[2] = '<u>\1</u>';
	$ubbs1[3] = '#\[center\](.*?)\[/center\]#si';
	$ubbs2[3] = '<center>\1</center>';
	$ubbs1[4] = '#\[url\]http://(.*?)\[/url\]#si';
	$ubbs2[4] = '<a href=\'http://\1\' target=\'_blank\'>http://\1</a>';
	$ubbs1[5] = '#\[url\](.*?)\[/url\]#si';
	$ubbs2[5] = '<a href=\'http://\1\' target=\'_blank\'>\1</a>';
	$ubbs1[6] = '#\[url=http://(.*?)\](.*?)\[/url\]#si';
	$ubbs2[6] = '<a href=\'http://\1\' target=\'_blank\'>\2</a>';
	$ubbs1[7] = '#\[url=(.*?)\](.*?)\[/url\]#si';
	$ubbs2[7] = '<a href=\'http://\1\' target=\'_blank\'>\2</a>';
	$ubbs1[8] = '#\[mail\](.*?)\[/mail\]#si';
	$ubbs2[8] = '<a href=\'mailto:\1\'>\1</a>';
	$ubbs1[9] = '#\[mail=(.*?)\](.*?)\[/mail\]#si';
	$ubbs2[9] = '<a href=\'mailto:\1\'>\2</a>';
	$ubbs1[10] = '#\[img\](.*?)\[/img\]#si';
	$ubbs2[10] = '<img src=\'\1\'>';
	$ubbs1[11] = '#\[small\](.*?)\[/small\]#si';
	$ubbs2[11] = '<span class=\'small\'>\1</span>';
	$ubbs1[12] = '#\[color=(.*?)\](.*?)\[/color\]#si';
	$ubbs2[12] = '<span style=\'color:\1\'>\2</span>';
	$ubbs1[13] = '#\[quote\](.*?)\[/quote\]#si';
	$ubbs2[13] = '<div class=\'quote\'>\1</div>';
	$ubbs1[14] = '#\[code\](.*?)\[/code\]#si';
	$ubbs2[14] = '<div class=\'quote\' style=\'width:400px;white-space:nowrap;overflow:auto\'><code style=\'white-space:nowrap\'>\1<br><br><br></code></div>';
	
	for ($i = 0; $ubbs1[$i] != ""; $i++)
	
	if ($ubbs1[$i] > $ubbs1[14] ) {
		break;
	} else {
		$message = preg_replace($ubbs1, $ubbs2, $message);
	}

	// Prevent use of mallicious javascript
	$text1[0] = "#document#si"; $text2[0] = 'docu<i></i>ment';
	$text1[1] = "#expression#si"; $text2[1] = 'expres<i></i>sion';
	$text1[2] = "#onmouseover#si"; $text2[2] = 'onmouse<i></i>over';
	$text1[3] = "#onclick#si"; $text2[3] = 'on<i></i>click';
	$text1[4] = "#onmousedown#si"; $text2[4] = 'onmouse<i></i>down';
	$text1[5] = "#onmouseup#si"; $text2[5] = 'onmouse<i></i>up';
	$text1[6] = "#ondblclick#si"; $text2[6] = 'on<i></i>dblclick';
	$text1[7] = "#onmouseout#si"; $text2[7] = 'onmouse<i></i>out';
	$text1[8] = "#onmousemove#si"; $text2[8] = 'onmouse<i></i>move';
	$text1[9] = "#onload#si"; $text2[9] = 'on<i></i>load';
	$text1[10] = "#background:url#si"; $text2[10] = 'background<i></i>:url';

	for ($i = 0; $text1[$i] != ""; $i++)
	
	if ($text1[$i] > $text1[10] ) {
		break;
	} else {
		$message = preg_replace($text1, $text2, $message);
	}

   return $message;
}

?>

<script>
function addText(elname, wrap1, wrap2) {
   if (document.selection) { // for IE
      var str = document.selection.createRange().text;
      document.forms['inputform'].elements[elname].focus();
      var sel = document.selection.createRange();
      sel.text = wrap1 + str + wrap2;
      return;
   } else if ((typeof document.forms['inputform'].elements[elname].selectionStart) != 'undefined') { // for Mozilla
      var txtarea = document.forms['inputform'].elements[elname];
      var selLength = txtarea.textLength;
      var selStart = txtarea.selectionStart;
      var selEnd = txtarea.selectionEnd;
      var oldScrollTop = txtarea.scrollTop;
      //if (selEnd == 1 || selEnd == 2)
      //selEnd = selLength;
      var s1 = (txtarea.value).substring(0,selStart);
      var s2 = (txtarea.value).substring(selStart, selEnd)
      var s3 = (txtarea.value).substring(selEnd, selLength);
      txtarea.value = s1 + wrap1 + s2 + wrap2 + s3;
      txtarea.selectionStart = s1.length;
      txtarea.selectionEnd = s1.length + s2.length + wrap1.length + wrap2.length;
      txtarea.scrollTop = oldScrollTop;
      txtarea.focus();
      return;
   } else {
      insertText(elname, wrap1 + wrap2);
   }
}

function insertText(elname, what) {
   if (document.forms['inputform'].elements[elname].createTextRange) {
      document.forms['inputform'].elements[elname].focus();
      document.selection.createRange().duplicate().text = what;
   } else if ((typeof document.forms['inputform'].elements[elname].selectionStart) != 'undefined') { // for Mozilla
      var tarea = document.forms['inputform'].elements[elname];
      var selEnd = tarea.selectionEnd;
      var txtLen = tarea.value.length;
      var txtbefore = tarea.value.substring(0,selEnd);
      var txtafter =  tarea.value.substring(selEnd, txtLen);
      var oldScrollTop = tarea.scrollTop;
      tarea.value = txtbefore + what + txtafter;
      tarea.selectionStart = txtbefore.length + what.length;
      tarea.selectionEnd = txtbefore.length + what.length;
      tarea.scrollTop = oldScrollTop;
      tarea.focus();
   } else {
      document.forms['inputform'].elements[elname].value += what;
      document.forms['inputform'].elements[elname].focus();
   }
}
</script>