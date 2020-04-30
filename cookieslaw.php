<?php
/**
 * cookieslaw.php
 *
 * Copyright 2014 - 2017 - www.eggemplo.com
 *
 * This code is provided subject to the license granted.
 * Unauthorized use and distribution is prohibited.
 * See COPYRIGHT.txt and LICENSE.txt
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This header and all notices must be kept intact.
 *
 * @author    eggemplo
 * @copyright 2014 - 2017 www.eggemplo.com
 * @license   See LICENSE.txt
 * @package cookieslaw
 * @since 1.0.0
 */

if (!defined('_PS_VERSION_'))
	exit;

class CookiesLaw extends Module
{
	private $_html = '';
	private $_postErrors = array();

	function __construct()
	{

		$this->name = 'cookieslaw';
		$this->tab = 'front_office_features';
		$this->version = '1.5.0';
		$this->author = 'eggemplo';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array(
				'min' => '1.5',
				'max' => _PS_VERSION_
		);
//		$this->bootstrap = true;

		$this->displayName = $this->l('Prestashop Cookies Law');
		$this->description = $this->l('According with 2012 European cookies law: Shows a text that explains that cookies are placed on the visitor\'s computer.');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		parent::__construct();

	}

	public function install()
	{
		if (parent::install() == false OR $this->registerHook('header') == false)
			return false;

		// Defaults
		Configuration::updateValue($this->name.'_cookieurl', 'index.php?id_cms=2&controller=cms');
		Configuration::updateValue($this->name.'_cookietop' , 'on');
		Configuration::updateValue($this->name.'_redirect', 'https://codecanyon.net/user/eggemplo/portfolio?ref=eggemplo');
		Configuration::updateValue($this->name.'_nothanks', true);
		Configuration::updateValue($this->name.'_noclickaccept', false);
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (Tools::isSubmit('submit'))
		{
			Configuration::updateValue($this->name.'_cookieurl', Tools::getValue('cookieurl'));
			Configuration::updateValue($this->name.'_cookietop', Tools::getValue('cookietop'));
			Configuration::updateValue($this->name.'_redirect', Tools::getValue('redirect'));
			Configuration::updateValue($this->name.'_nothanks', Tools::getValue('nothanks'));
			Configuration::updateValue($this->name.'_noclickaccept', Tools::getValue('noclickaccept'));
			$this->_html .= '<div class="conf ok">'.$this->l('Updated').'</div>';
		}

		return $this->_displayForm();
	}

	private function _displayForm()
	{
		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" alt="" class="middle" />'.$this->l('Cookies Law').'</legend>

				<label>'.$this->l('Top of screen').'</label>
				<div class="margin-form">
					<input type="checkbox" name="cookietop"'.(Tools::getValue('cookietop', Configuration::get($this->name.'_cookietop')) ? ' checked="checked"' : '').' />
					<p class="clear">'.$this->l('Displays the cookie law either at top or bottom of the screen.').'</p>
				</div>
				
				<label>'.$this->l('URL to your cms cookie policy').'</label>
				<div class="margin-form">
					<input type="text" name="cookieurl" value="'.Tools::getValue('cookieurl', Configuration::get($this->name.'_cookieurl')).'"/>
					<p class="clear">'.$this->l('Supply the url of the CMS page containing your cookie policy.').'</p>
				</div>

				<label>'.$this->l('Display "No, thanks" button').'</label>
				<div class="margin-form">
					<input type="checkbox" name="nothanks"'.(Tools::getValue('nothanks', Configuration::get($this->name.'_nothanks')) ? ' checked="checked"' : '').' />
				</div>

				<label>'.$this->l('"No, thanks" redirect to').'</label>
				<div class="margin-form">
					<input type="text" name="redirect" value="'.Tools::getValue('redirect', Configuration::get($this->name.'_redirect')).'"/>
					<p class="clear">'.$this->l('If a user does not agree, then they will be directed away from the site to here.').'</p>
				</div>

				<label>'.$this->l('No click is accept?').'</label>
				<div class="margin-form">
					<input type="checkbox" name="noclickaccept"'.(Tools::getValue('noclickaccept', Configuration::get($this->name.'_noclickaccept')) ? ' checked="checked"' : '').' />
					<p class="clear">'.$this->l('It isn\'t necesary to click on the button to accept the cookies. They are accepted if the user continuous.').'</p>
				</div>

				<input type="submit" name="submit" value="'.$this->l('Save').'" class="button" />
			</fieldset>
		</form>';

		return $this->_html;
	}

	function hookHeader($params)
	{
		global $smarty, $cookie;

		$this->context->controller->addCSS($this->_path.'/views/css/cookieslaw.css', 'all');
		$this->context->controller->addJS($this->_path.'/views/js/cookieslaw.js');

		$cookie_path = trim(__PS_BASE_URI__, '/\\').'/';
		if ($cookie_path{0} != '/') $cookie_path = '/'.$cookie_path;
		$cookie_path = rawurlencode($cookie_path);
		$cookie_path = str_replace('%2F', '/', $cookie_path);
		$cookie_path = str_replace('%7E', '~', $cookie_path);

		$smarty->assign(array(
			'cl_RedirectLink' => Configuration::get($this->name.'_redirect'),
			'cl_CookieTop' => Configuration::get($this->name.'_cookietop'),
			'cl_CookieUrl' => Configuration::get($this->name.'_cookieurl'),
			'cl_CookieNoThanks' => Configuration::get($this->name.'_nothanks'),
			'cl_CookieNoClickAccept' => Configuration::get($this->name.'_noclickaccept'),
			'cl_CookieName' => 'prestashopcookieslaw',
			'cl_CookiePath' => $cookie_path,
			'cl_CookieDomain' => Tools::getShopDomainSsl(true),
			'cl_ajaxUrl' => Tools::getHttpHost(true)._MODULE_DIR_.'cookieslaw/cookie_ajax.php'
		));

		return $this->display(__FILE__, 'cookieslaw.tpl');
	}

}