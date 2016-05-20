<?php

	$store_cfg = Op5Config::instance()->getConfig('ninja_menu');
	$groups = op5auth::instance()->get_user()->get_groups();

	/* Use settings */
	$orientation = (isset($orientation)) ? $orientation : 'left';

	if (!$store_cfg)
		$store_cfg = array();

	$config = array();
	foreach ($store_cfg as $group => $sections) {
		if (in_array($group, $groups)) {
			foreach ($sections as $section => $items) {
				$config = array_merge($config, $items);
			}
		}
	}

	$render_menu = function ($menu, $is_root = false) use (&$config, &$render_menu) {
		$branch = $menu->get_branch();
		$attr   = $menu->get_attributes();
		$icon   = $menu->get_icon();

		if (substr($icon, -4) == '.png')
			$icon = sprintf('<img src="%s">', htmlentities($icon));
		else if ($icon != '')
			$icon = sprintf('<span class="%s"></span>', htmlentities($menu->get_icon()));

		if (!is_null($menu->get_href())) {
			$href = $menu->get_href();

			if (!preg_match('/^http/', $href) && !preg_match('/^\//', $href)) {
				$href = url::base(true) . $href;
			}
			$attr['href'] = $href;
		}

		$attributes = "";
		foreach ($attr as $name => $value) {
			$attributes .= sprintf(" %s=\"%s\"", htmlentities($name), htmlentities($value));
		}

		if (!is_null($menu->get_href())) {
			$render = sprintf('<a%s>%s</a>', $attributes, $icon);
		} else {
			$render = sprintf(
				'<a%s>%s<span>%s</span></a>',
				$attributes,
				$icon,
				$menu->get_label_as_html()
			);
		}

		if ($menu->has_children()) {
			$render .= '<ul>';
			foreach ($branch as $child) {
				if (in_array($child->get_id(), $config)) { continue; }
				$cAttributes = $child->get_attributes();
				$render .= '<li tabindex="1">' . $render_menu($child, false) . '</li>';
			}
			$render .= '</ul>';
		}
		return $render;
	};

	echo "<div class=\"image-menu $class menu-$orientation\">";
	echo $render_menu($menu);
	echo "</div>";