<ul class="lang_switch">
	<li>Sprache/Language:</li>
	<?php foreach(c::get('lang.available') as $lang) : ?>
		<?php if($lang != c::get('lang.current')) : ?>
			<?php if($page->files()->find($page->template().'.'.$lang.'.md')) : ?>
				<li><a href="<?php echo $page->url($lang) ?>" class="<?php echo $lang; ?>"><?php echo $lang; ?></a></li>
			<?php else: ?>
				<li><?php echo $lang; ?></li>
			<?php endif; ?>
		<?php else: ?>
			<li class="active"><a href="<?php echo $page->url($lang) ?>" class="<?php echo $lang; ?>"><?php echo $lang; ?></a></li>
		<?php endif; ?>
	<?php endforeach; ?>
</ul>