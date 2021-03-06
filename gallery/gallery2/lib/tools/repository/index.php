<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */

 /**
 * @package RepositoryTools
 */
define('G2_SUPPORT_URL_FRAGMENT', '../../support/');

include('../../support/security.inc');
include('../../../bootstrap.inc');
require_once('../../../init.inc');
define('GALLERY_MAIN_PHP', 'index.php');

/* Simulate HTTP for command line clients */
if (php_sapi_name() == 'cli') {
    $argv = GalleryUtilities::getServerVar('argv');
    for ($i = 1; $i < count($argv); $i++) {
	$arg = split('=', $argv[$i]);
	GalleryUtilities::putRequestVariable($arg[0], $arg[1], false);
    }
}

function RepositoryToolsMain() {
    $ret = GalleryInitFirstPass();
    if ($ret) {
	return $ret;
    }

    $ret = GalleryInitSecondPass();
    if ($ret) {
	return $ret;
    }

    global $gallery;

    if (php_sapi_name() == 'cli') {
	$isSiteAdmin = true;
    } else {
	list ($ret, $isSiteAdmin) = GalleryCoreApi::isUserInSiteAdminGroup();
	if ($ret) {
	    return $ret;
	}
    }

    GalleryCoreApi::requireOnce(
	'lib/tools/repository/classes/RepositoryControllerAndView.class');

    /* Set repository configuration data. */
    $repositoryPath = $gallery->getConfig('data.gallery.base') . '/repository/';
    $gallery->setConfig('repository.path', $repositoryPath);
    $gallery->setConfig('repository.templates', 'lib/tools/repository/templates/');

    if ($isSiteAdmin) {
	/* Verify our repository structure exists */
	$platform =& $gallery->getPlatform();
	foreach (array($repositoryPath,
		       $repositoryPath . '/modules',
		       $repositoryPath . '/themes') as $path) {
	    if (!$platform->file_exists($path)) {
		if (!$platform->mkdir($path)) {
		    return GalleryCoreApi::error(ERROR_PLATFORM_FAILURE, __FILE__, __LINE__,
						 "Unable to create directory: $path");
		}
	    }
	}

	/* Load controller. */
	$controllerName = GalleryUtilities::getRequestVariables('controller');
	if (!preg_match('/^[A-Za-z]+$/', $controllerName)) {
	    return GalleryCoreApi::error(ERROR_BAD_PARAMETER, __FILE__, __LINE__,
					 "Bad controller '$controllerName'");
	}
	$methodName = GalleryUtilities::getRequestVariables('action');
	$controllerPath = sprintf('%s/%s.inc', dirname(__FILE__), $controllerName);
    }

    /* Configure our url Generator for repository mode. */
    $urlGenerator = new GalleryUrlGenerator();
    $ret = $urlGenerator->init();
    if ($ret) {
	return $ret;
    }
    $gallery->setUrlGenerator($urlGenerator);

    $platform =& $gallery->getPlatform();
    if (!$isSiteAdmin || !$platform->file_exists($controllerPath)) {
	/* Set default controller. */
	$controllerName = 'MainPage';
	$controllerPath = sprintf('%s/%s.inc', dirname(__FILE__), $controllerName);
	$methodName = 'showAvailableActions';
    }

    $controllerClassName = $controllerName . 'ControllerAndView';
    require_once($controllerPath);
    $controller = new $controllerClassName();
    $controller->init();

    /* Call a controller method. */
    if (!method_exists($controller, $methodName)) {
	return GalleryCoreApi::error(ERROR_BAD_PARAMETER, __FILE__, __LINE__,
				    "Method [$methodName] does not exist in $controllerClassName");
    }

    $ret = $controller->$methodName();
    if ($ret) {
	return $ret;
    }

    return null;
}

$ret = RepositoryToolsMain();
if ($ret) {
    print $ret->getAsHtml();
    print $gallery->getDebugBuffer();
    return;
}
?>
