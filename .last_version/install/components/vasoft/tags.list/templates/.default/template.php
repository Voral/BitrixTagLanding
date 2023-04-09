<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);
$frame = $this->createFrame()->begin();
if (count($arResult)): ?>
	<div class="vs-tags-list">
		<? foreach ($arResult as $arTag): ?>
			<a href="<?= $arTag['DETAIL_PAGE_URL'] ?>"><?= $arTag['NAME'] ?></a>
		<? endforeach; ?>
	</div>
<? endif ?>
<? $frame->end(); ?>