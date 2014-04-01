<?php
namespace Craft;

use \Mockery as m;

class MinimeeRemoteAssetModelTest extends BaseTest
{
	protected $_model;

	/**
	 * Called at the start of each test run; helps bootstrap our tests
	 *
	 * @return void
	 */
	public function setUp()
	{
		require_once __DIR__ . '/../vendor/autoload.php';
	}

	public function testExistsIsAlwaysTrue()
	{
		$this->_populateWith(array());

		$this->assertSame(true, $this->_model->exists());
	}

	public function testToStringReturnsFilename()
	{
		$this->_populateWith(array(
			'filename' => 'http://domain.com/assets/style.css'
		));

		$this->assertEquals('http://domain.com/assets/style.css', sprintf($this->_model));
	}

	public function testSetFilenamePathRemovesDoubleSlashes()
	{
		$this->_populateWith(array());

		$this->_model->filenamePath = 'http://domain.com///cache';
		$this->assertEquals('http://domain.com/cache', $this->_model->filenamePath);
	}

	public function testSetFilenamePathRemovesDoubleSlashesProtocolRelative()
	{
		$this->_populateWith(array());

		$this->_model->filenameUrl = '//domain.com///cache';
		$this->assertEquals('//domain.com/cache', $this->_model->filenameUrl);
	}

	public function testSetFilenameUrlRemovesDoubleSlashes()
	{
		$this->_populateWith(array());

		$this->_model->filenameUrl = 'http://domain.com///cache';
		$this->assertEquals('http://domain.com/cache', $this->_model->filenameUrl);
	}

	public function testSetFilenameUrlRemovesDoubleSlashesProtocolRelative()
	{
		$this->_populateWith(array());

		$this->_model->filenameUrl = '//domain.com///cache';
		$this->assertEquals('//domain.com/cache', $this->_model->filenameUrl);
	}

	protected function _inspect($data)
	{
		fwrite(STDERR, print_r($data));
	}

	/**
	 * Internal method for shorthand populating our Minimee_RemoteAssetModel
	 * 
	 * @param Array $attributes
	 * @return Minimee_RemoteAssetModel
	 */
	protected function _populateWith($attributes)
	{
		$this->_model = Minimee_RemoteAssetModel::populateModel($attributes);
	}
}