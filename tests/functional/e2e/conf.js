/*global
  describe: false,
  protractor: false
*/
/*jshint
  node: true,
  strict: false
*/
/**
 * @fileOverview
 * An example Protractor configuration that uses a Selenium server at
 * selenium.example.com to run tests against a web application hosted
 * on testapp.example.com.
 */
 
exports.config = {
 
  // -----------------------------------------------------------------
  // Selenium Setup: An existing Selenium standalone server.
  // -----------------------------------------------------------------
 
  // The address of an existing selenium server that Protractor will use.
  //
  // Note that this server must have chromedriver in its path for Chromium
  // tests to work.
  seleniumAddress: 'http://selenium.example.com:4444/wd/hub',
 
  // -----------------------------------------------------------------
  // Specify the test code that will run.
  // -----------------------------------------------------------------
 
  // Spec patterns are relative to the location of this config.
  specs: ['spec.js'],
 
  // -----------------------------------------------------------------
  // Browser and Capabilities
  // -----------------------------------------------------------------
 
  // For a full list of available capabilities, see
  //
  // https://code.google.com/p/selenium/wiki/DesiredCapabilities
 
  // -----------------------------------------------------------------
  // Browser and Capabilities: PhantomJS
  // -----------------------------------------------------------------
 
  // Blocking issues prevent most uses of PhantomJS and Protractor as of
  // Q4 2013. See, for example:
  //
  // https://github.com/angular/protractor/issues/85
  //
  // It is also hard to pass through needed command line parameters.
 
  /*
  capabilities: {
    browserName: 'phantomjs',
    version: '',
    platform: 'ANY'
  },
  */
 
  // -----------------------------------------------------------------
  // Browser and Capabilities: Chrome
  // -----------------------------------------------------------------
 
  capabilities: {
    browserName: 'chromium-browser',
    version: '',
    platform: 'ANY'
  },
 
  // -----------------------------------------------------------------
  // Browser and Capabilities: Firefox
  // -----------------------------------------------------------------
 
  /*
  capabilities: {
    browserName: 'firefox',
    version: '',
    platform: 'ANY'
  },
  */
 
  // -----------------------------------------------------------------
  // Application configuration.
  // -----------------------------------------------------------------
 
  // A base URL for your application under test. Calls to browser.get()
  // with relative paths will be prepended with this.
  baseUrl: 'http://testapp.example.com/index.html',
 
  // Selector for the element housing the angular app - this defaults to
  // body, but is necessary if ng-app is on a descendant of 
  rootElement: 'body',
 
  // -----------------------------------------------------------------
  // Other configuration.
  // -----------------------------------------------------------------
 
  // The timeout for each script run on the browser. This should be longer
  // than the maximum time your application needs to stabilize between tasks.
  allScriptsTimeout: 11000,
 
  /**
   * A callback function called once protractor is ready and available,
   * and before the specs are executed.
   *
   * You can specify a file containing code to run by setting onPrepare to
   * the filename string.
   */
  onPrepare: function() {
    // At this point, global 'protractor' object will be set up, and
    // jasmine will be available.
  },
 
  // ----- Options to be passed to minijasminenode -----
  jasmineNodeOpts: {
    /**
     * onComplete will be called just before the driver quits.
     */
    onComplete: function () {},
    // If true, display spec names.
    isVerbose: true,
    // If true, print colors to the terminal.
    showColors: true,
    // If true, include stack traces in failures.
    includeStackTrace: true,
    // Default time to wait in ms before a test fails.
    defaultTimeoutInterval: 30000
  }
};