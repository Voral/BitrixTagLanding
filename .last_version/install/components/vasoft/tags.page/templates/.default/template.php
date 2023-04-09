<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

$this->setFrameMode(true);
$frame = $this->createFrame()->begin();
?>
<? if (count($arResult['ITEMS'])): ?>
	<div class="vs-tags-result">
		<? foreach ($arResult['ITEMS'] as $arItem): ?>
			<div class="vs-tags-item">
				<a href="<?= $arItem['URL'] ?>" class="vs-tags-title"><?= $arItem['TITLE_FORMATED'] ?></a>
				<div class="vs-tags-body"><?= $arItem['BODY_FORMATED'] ?></div>
				<div class="vs-tags-more">
					<a href="<?= $arItem['URL'] ?>"><?= Loc::getMessage("VASOFT_TAGS_MORE") ?></a>
				</div>
			</div>
		<? endforeach; ?>
	</div>
<? elseif (count($arResult['CLOUD'])): ?>
	<div class="vs-tags-cloud">
		<? foreach ($arResult['CLOUD'] as $arTag): ?>
			<a href="<?= $arTag['URL'] ?>" style="font-size:<?=$arTag['KOEF']?>%"><?= $arTag['NAME'] ?></a>
		<? endforeach ?>
	</div>
<? endif ?>
<? $frame->end(); ?>
<? if ($arResult['TEXT'] != ''): ?>
	<div class="vs-tags-text">
		<?= $arResult['TEXT'] ?>
	</div>
<? endif ?>
