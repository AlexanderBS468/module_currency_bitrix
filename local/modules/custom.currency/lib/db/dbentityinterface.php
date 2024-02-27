<?php

namespace Custom\Currency\DB;

interface DBEntityInterface
{

	public function install() : void;

	public function uninstall() : void;
}