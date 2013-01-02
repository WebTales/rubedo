<?php




class UrlTest extends PHPUnit_Framework_TestCase
{

    /**
     * Cleaning
     */
    public function tearDown() {
        Rubedo\Services\Manager::resetMocks();
        parent::tearDown();
    }


    /**
     * init the Zend Application for tests
     */
    public function setUp() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		$this->bootstrap->bootstrap();
		
        $this->_mockPageContentService = $this->getMock('Rubedo\\Content\\Page');
        Rubedo\Services\Manager::setMockService('PageContent', $this->_mockPageContentService);
		
		 $this->_mockSitesService = $this->getMock('Rubedo\\Collection\\Sites');
        Rubedo\Services\Manager::setMockService('Sites', $this->_mockSitesService);
		
		 $this->_mockPagesService = $this->getMock('Rubedo\\Collection\\Pages');
        Rubedo\Services\Manager::setMockService('Pages', $this->_mockPagesService);
		
        parent::setUp();
    }
	
	/**
	 * Check if "currentSite" is called when invoking singleUrl without a siteId
	 */
	public function testDoSingleUrlCallCurrentSiteIfNoSiteGiven()
	{
		$this->_mockPageContentService->expects($this->once())
                 ->method('getCurrentSite');
				 
		
		$urlService = new Rubedo\Router\Url();
		$urlService->displaySingleUrl('toto');
		
		
	}
	
	/**
	 * Check if "currentSite" is not called when invoking singleUrl with a siteId
	 */
	public function testDoSingleUrlNotCallCurrentSiteIfSiteGiven()
	{
		$this->_mockPageContentService->expects($this->never())
                 ->method('getCurrentSite');
				 
		
		$urlService = new Rubedo\Router\Url();
		$urlService->displaySingleUrl('toto','siteId');
		
		
	}
	/**
	 * Check if "getHost" is called once when invoking singleUrl with a siteId
	 */
	public function testDoSingleUrlCallGetHostIfSiteGiven()
	{
	$data["id"]="page";
	$data["site"]="siteId";
	$data["text"]="text";
	$this->_mockPagesService->expects($this->once())
                 ->method('findByNameAndSite')
				 ->with($this->equalTo('single'),$this->equalTo("siteId"))
				 ->will($this->returnValue($data));
	$this->_mockSitesService->expects($this->once())
                 ->method('getHost')
				 ->with("siteId");
				 
		$urlService = new Rubedo\Router\Url();
		$urlService->displaySingleUrl('Content','siteId');
	}	
	public function testDoSingleUrlNotCallGetHostIfNoSiteGiven()
	{
	$this->_mockSitesService->expects($this->never())
                 ->method('getHost');
				 
		$urlService = new Rubedo\Router\Url();
		$urlService->displaySingleUrl('Content','siteId');
	}	
}