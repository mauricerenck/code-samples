<?php
	$lang = c::get('lang.current');
	$other = ($lang == 'de') ? 'en' : 'de';
	$template = $page->template();

	if($page->files()->find($template.'.'.$lang.'.md') == NULL) {
		header ('HTTP/1.1 303 See Other');
		header('Location: '.$page->url($other));
	}
?>
<html lang="<?php echo c::get('lang.current'); ?>">
<!-- your header-stuff here -->