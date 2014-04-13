<?php
namespace Craft;

use \Mockery as m;

class MinimeeServiceTest extends BaseTest
{
	public function setUp()
	{
		$_SERVER['SERVER_SOFTWARE'] = 'Apache';

		$this->autoload();

        // $this->config = m::mock('Craft\ConfigService');
        // $this->config->shouldReceive('getIsInitialized')->andReturn(true);
        // $this->config->shouldReceive('get')->with('usePathInfo')->andReturn(true)->byDefault();
        // $this->config->shouldReceive('get')->with('translationDebugOutput')->andReturn(false)->byDefault();
        // $this->config->shouldReceive('get')->with('resourceTrigger')->andReturn('resource')->byDefault();
        // $this->config->shouldReceive('get')->with('version')->andReturn('2.0');

        // $this->setComponent(craft(), 'config', $this->config);

		minimee()->stash('plugin', new MinimeePlugin);
		minimee()->stash('service', new MinimeeService);

		// TODO: figure outo how to propery mock config so that we can run init()
		//minimee()->service->init();
	}

	public function testMakeHashOfCacheBase()
	{
		minimee()->service->cacheBase = 'asdf1234';

		$makeHashOfCacheBase = $this->getMethod(minimee()->service, 'makeHashOfCacheBase');
		$hashOfCacheBase = $makeHashOfCacheBase->invoke(minimee()->service);

		$this->assertEquals(sha1(minimee()->service->cacheBase), $hashOfCacheBase);
	}

	public function testSetAssetsWhenLocalCss()
	{
		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$setAssets->invokeArgs(minimee()->service, array(
			'/assets/css/style.css'
		));

		$getAssets = minimee()->service->assets;

		$this->assertInstanceOf('\Craft\Minimee_LocalAssetModel', $getAssets[0]);
	}

	public function testSetAssetsWhenLocalJs()
	{
		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$setAssets->invokeArgs(minimee()->service, array(
			'/assets/js/app.js'
		));

		$getAssets = minimee()->service->assets;

		$this->assertInstanceOf('\Craft\Minimee_LocalAssetModel', $getAssets[0]);
	}

	public function testSetAssetsWhenRemoteCss()
	{
		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$setAssets->invokeArgs(minimee()->service, array(
			'http://domain.dev/assets/css/style.css'
		));

		$getAssets = minimee()->service->assets;

		$this->assertInstanceOf('\Craft\Minimee_RemoteAssetModel', $getAssets[0]);
	}

	public function testSetAssetsWhenRemoteJs()
	{
		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$setAssets->invokeArgs(minimee()->service, array(
			'http://domain.dev/assets/js/app.js'
		));

		$getAssets = minimee()->service->assets;

		$this->assertInstanceOf('\Craft\Minimee_RemoteAssetModel', $getAssets[0]);
	}

	public function testSetAssetsWhenMixedLocalAndRemoteCss()
	{
		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$setAssets->invokeArgs(minimee()->service, array(
			array(
				'/assets/js/jquery.js',
				'http://domain.dev/assets/js/app.js'
			)
		));

		$getAssets = minimee()->service->assets;

		$this->assertInstanceOf('\Craft\Minimee_LocalAssetModel', $getAssets[0]);
		$this->assertInstanceOf('\Craft\Minimee_RemoteAssetModel', $getAssets[1]);
	}

	public function testSetAssetsWhenMixedLocalAndRemoteJs()
	{
		$setAssets = $this->getMethod(minimee()->service, 'setAssets');
		$setAssets->invokeArgs(minimee()->service, array(
			array(
				'/assets/css/normalize.css',
				'http://domain.dev/assets/css/style.css'
			)
		));

		$getAssets = minimee()->service->assets;

		$this->assertInstanceOf('\Craft\Minimee_LocalAssetModel', $getAssets[0]);
		$this->assertInstanceOf('\Craft\Minimee_RemoteAssetModel', $getAssets[1]);
	}

	public function testSetTypeAllEnums()
	{
		$setType = $this->getMethod(minimee()->service, 'setType');
		$setType->invokeArgs(minimee()->service, array(MinimeeType::Css));
		$this->assertSame(MinimeeType::Css, minimee()->service->type);

		$setType->invokeArgs(minimee()->service, array(MinimeeType::Js));
		$this->assertSame(MinimeeType::Js, minimee()->service->type);
	}

	/**
     * @expectedException Exception
     */
    public function testSetTypeInvalid()
    {
		$setType = $this->getMethod(minimee()->service, 'setType');
		$setType->invokeArgs(minimee()->service, array('CSS'));
    }

	public function testIsCombineEnabledWhenTrue()
	{
		minimee()->service->type = 'css';
		minimee()->service->settings->combineCssEnabled = true;

		$isCombineEnabled = $this->getMethod(minimee()->service, 'isCombineEnabled');

		$this->assertTrue($isCombineEnabled->invoke(minimee()->service));
	}

	public function testIsCombineEnabledWhenFalse()
	{
		minimee()->service->type = 'css';
		minimee()->service->settings->combineCssEnabled = false;

		$isCombineEnabled = $this->getMethod(minimee()->service, 'isCombineEnabled');

		$this->assertFalse($isCombineEnabled->invoke(minimee()->service));
	}

	public function testIsCombineEnabledNullWithoutType()
	{
		$reset = $this->getMethod(minimee()->service, 'reset');
		$reset->invoke(minimee()->service);

		$isCombineEnabled = $this->getMethod(minimee()->service, 'isCombineEnabled');

		$this->assertEquals(null, $isCombineEnabled->invoke(minimee()->service));
	}

	public function testSetMaxCacheTimestampAlwaysSetsMax()
	{
		$setMaxCacheTimestamp = $this->getMethod(minimee()->service, 'setMaxCacheTimestamp');

		$dt = new DateTime('now');
		$nowTimestamp = $dt->getTimestamp();

		$setMaxCacheTimestamp->invokeArgs(minimee()->service, array($dt));
		$this->assertEquals($nowTimestamp, minimee()->service->cacheTimestamp);

		// reduce by a day
		$dt->modify("-1 day");
		$yesterdayTimestamp = $dt->getTimestamp();

		$setMaxCacheTimestamp->invokeArgs(minimee()->service, array($dt));
		$this->assertEquals($nowTimestamp, minimee()->service->cacheTimestamp);

		// increase by 2 days
		$dt->modify("+2 day");
		$tomorrowTimestamp = $dt->getTimestamp();

		$setMaxCacheTimestamp->invokeArgs(minimee()->service, array($dt));
		$this->assertEquals($tomorrowTimestamp, minimee()->service->cacheTimestamp);

		// test that setting it to the same value has no ill effect
		$setMaxCacheTimestamp->invokeArgs(minimee()->service, array($dt));
		$this->assertEquals($tomorrowTimestamp, minimee()->service->cacheTimestamp);
	}

	public function testGetCacheTimestampWhenZeroReturnsPaddedZeros()
	{
		$getCacheTimestamp = $this->getMethod(minimee()->service, 'getCacheTimestamp');
		$this->assertEquals(MinimeeService::TimestampZero, $getCacheTimestamp->invoke(minimee()->service));
	}

	public function testMakeTagsByTypePassingCssStringUsingDefaultTemplate()
	{
		$css = 'http://domain.dev/cache/hash.timestamp.css';
		$cssTagTemplate = minimee()->service->settings->cssTagTemplate;

		$rendered = sprintf($cssTagTemplate, $css);
		$this->assertEquals($rendered, minimee()->service->makeTagsByType('css', $css));
	}

	public function testMakeTagsByTypePassingCssArrayUsingDefaultTemplate()
	{
		$cssArray = array(
			'http://domain.dev/cache/hash1.timestamp.css',
			'http://domain.dev/cache/hash2.timestamp.css'
		);
		$cssTagTemplate = minimee()->service->settings->cssTagTemplate;

		$rendered = '';
		foreach($cssArray as $css)
		{
			$rendered .= sprintf($cssTagTemplate, $css);
		}

		$this->assertEquals($rendered, minimee()->service->makeTagsByType('css', $cssArray));
	}

	public function testMakeTagsByTypePassingCssStringUsingCustomTemplate()
	{
		$css = 'http://domain.dev/cache/hash.timestamp.css';
		$cssTagTemplate = '<link rel="stylesheet" type="text/css" media="screen" href="%s"/>';

		minimee()->service->settings->cssTagTemplate = $cssTagTemplate;

		$rendered = sprintf($cssTagTemplate, $css);

		$this->assertEquals($rendered, minimee()->service->makeTagsByType('css', $css));

	}

	public function testMakeTagsByTypePassingCssArrayUsingCustomTemplate()
	{
		$cssArray = array(
			'http://domain.dev/cache/hash1.timestamp.css',
			'http://domain.dev/cache/hash2.timestamp.css'
		);
		$cssTagTemplate = '<link rel="stylesheet" type="text/css" media="screen" href="%s"/>';

		minimee()->service->settings->cssTagTemplate = $cssTagTemplate;

		$rendered = '';
		foreach($cssArray as $css)
		{
			$rendered .= sprintf($cssTagTemplate, $css);
		}

		$this->assertEquals($rendered, minimee()->service->makeTagsByType('css', $cssArray));
	}

	public function testMakeTagsByTypePassingJsStringUsingDefaultTemplate()
	{
		$js = 'http://domain.dev/cache/hash.timestamp.js';
		$jsTagTemplate = minimee()->service->settings->jsTagTemplate;

		$rendered = sprintf($jsTagTemplate, $js);
		$this->assertEquals($rendered, minimee()->service->makeTagsByType('js', $js));
	}

	public function testMakeTagsByTypePassingJsArrayUsingDefaultTemplate()
	{
		$jsArray = array(
			'http://domain.dev/cache/hash1.timestamp.js',
			'http://domain.dev/cache/hash2.timestamp.js'
		);
		$jsTagTemplate = minimee()->service->settings->jsTagTemplate;

		$rendered = '';
		foreach($jsArray as $js)
		{
			$rendered .= sprintf($jsTagTemplate, $js);
		}

		$this->assertEquals($rendered, minimee()->service->makeTagsByType('js', $jsArray));
	}

	public function testMakeTagsByTypePassingJsStringUsingCustomTemplate()
	{
		$js = 'http://domain.dev/cache/hash.timestamp.js';
		$jsTagTemplate = '<script src="%s" type="text/javascript" defer></script>';

		minimee()->service->settings->jsTagTemplate = $jsTagTemplate;

		$rendered = sprintf($jsTagTemplate, $js);

		$this->assertEquals($rendered, minimee()->service->makeTagsByType('js', $js));
	}

	public function testMakeTagsByTypePassingJsArrayUsingCustomTemplate()
	{
		$jsArray = array(
			'http://domain.dev/cache/hash1.timestamp.js',
			'http://domain.dev/cache/hash2.timestamp.js'
		);
		$jsTagTemplate = '<script src="%s" type="text/javascript" defer></script>';

		minimee()->service->settings->jsTagTemplate = $jsTagTemplate;

		$rendered = '';
		foreach($jsArray as $js)
		{
			$rendered .= sprintf($jsTagTemplate, $js);
		}

		$this->assertEquals($rendered, minimee()->service->makeTagsByType('js', $jsArray));
	}

	public function testReset()
	{
		minimee()->service->assets = array('/asset/css/style.css');
		minimee()->service->type = MinimeeType::Css;
		minimee()->service->settings = array(
			'enabled' => true);
		minimee()->service->cacheBase = 'asset.css.style.css';
		minimee()->service->cacheTimestamp = new DateTime('now');

		$reset = $this->getMethod(minimee()->service, 'reset');
		$reset->invoke(minimee()->service);

		$this->assertSame(array(), minimee()->service->assets);
		$this->assertInstanceOf('\Craft\Minimee_SettingsModel', minimee()->service->settings);
		$this->assertSame('', minimee()->service->type);
		$this->assertSame('', minimee()->service->cacheBase);
		$this->assertSame(MinimeeService::TimestampZero, minimee()->service->cacheTimestamp);
	}

	public function dataProviderInvalidUrls()
	{
		return [
			['domain.com'],
			['/domain.com']
		];
	}

	/**
	 * @dataProvider dataProviderInvalidUrls
	 */
	public function testIsUrlInvalid($url)
	{
		$isUrl = $this->getMethod(minimee()->service, 'isUrl');
		$this->assertFalse($isUrl->invokeArgs(minimee()->service, array($url)));
	}

	public function dataProviderValidUrls()
	{
		return [
			['http://domain.com'],
			['https://domain.com'],
			['//domain.com']
		];
	}

	/**
	 * @dataProvider dataProviderValidUrls
	 */
	public function testIsUrlValid($url)
	{
		$isUrl = $this->getMethod(minimee()->service, 'isUrl');
		$this->assertTrue($isUrl->invokeArgs(minimee()->service, array($url)));
	}

	protected function autoload()
	{
		// our tests use this
		require_once __DIR__ . '/../../library/vendor/autoload.php';

		// These are usually automatically loaded by Craft
		Craft::import('plugins.minimee.MinimeePlugin');
		Craft::import('plugins.minimee.services.MinimeeService');

		// This is loaded via MinimeePlugin::init()
		Craft::import('plugins.minimee.enums.MinimeeType');

		// this usually happens in MinimeePlugin::init()
		require_once __DIR__ . '/../vendor/autoload.php';

		// And these Craft can usually autoload
		// require_once __DIR__ . '/../../models/Minimee_BaseAssetModel.php';
		// require_once __DIR__ . '/../../models/Minimee_LocalAssetModel.php';
		// require_once __DIR__ . '/../../models/Minimee_RemoveAssetModel.php';
		// require_once __DIR__ . '/../../models/Minimee_SettingsModel.php';
	}
}

/**
 * A way to grab the dependency container within the Craft namespace
 */
if (!function_exists('\\Craft\\minimee'))
{
	function minimee()
	{
		return \SelvinOrtiz\Zit\Zit::getInstance();
	}
}