<?php
ini_set('max_execution_time', 600);
ini_set('memory_limit', '64M');


/** GwtCrawlErrors
 *  ================================================================================
 *  PHP class to download crawl errors from Google webmaster tools as csv.
 *  ================================================================================
 *  @category
 *  @package     GwtCrawlErrors
 *  @copyright   2013 - present, Stephan Schmitz
 *  @license     http://eyecatchup.mit-license.org
 *  @version     CVS: $Id: GwtCrawlErrors.class.php, v1.0.0 Rev 10 2013/04/14 19:15:43 ssc Exp $
 *  @author      Stephan Schmitz <eyecatchup@gmail.com>
 *  @link        https://github.com/eyecatchup/GWT_CrawlErrors-php/
 *  ================================================================================
 *  LICENSE: Permission is hereby granted, free of charge, to any person obtaining
 *  a copy of this software and associated documentation files (the "Software'),
 *  to deal in the Software without restriction, including without limitation the
 *  rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *    The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY
 *  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *  ================================================================================
 */

class Cms_Gdata_Crawlerror
{
    const HOST = "https://www.google.com";
    const SERVICEURI = "/webmasters/tools/";

    public function __construct()
    {
        $this->_auth = $this->_loggedIn = $this->_domain = false;
        $this->_data = array();
    }

    public function getArray($domain)
    {
        if ($this->_validateDomain($domain)) {
            if ($this->_prepareData()) {
                return $this->_data();
            }
            else {
                throw new Exception('Error receiving crawl issues for ' . $domain);
            }
        }
        else {
            throw new Exception('The given domain is not connected to your Webmastertools account!');
            exit;
        }
    }

    /**
     * 获取数据
     * @param string $domain
     * @param string $localPath
     * @throws Exception
     */
	public function getData($domain, $localPath = false)
    {
        if ($this->_validateDomain($domain)) {
            if ($this->_prepareData()) {
                if (!$localPath) {
                    $this->_HttpHeader();
                    $this->_output();
                }
                else {
                    $this->_output($localPath);
                }
            }
            else {
                throw new Exception('Error receiving crawl issues for ' . $domain);
            }
        }
        else {
            throw new Exception('The given domain is not connected to your Webmastertools account!');
            exit;
        }
    }

    public function getSites()
    {
        if($this->_loggedIn) {
            $feed = $this->_getData('feeds/sites/');
            if ($feed) {
                $doc = new DOMDocument();
                $doc->loadXML($feed);

                $sites = array();
                foreach ($doc->getElementsByTagName('entry') as $node) {
                    array_push($sites,
                      $node->getElementsByTagName('title')->item(0)->nodeValue);
                }

                return (0 < sizeof($sites)) ? $sites : false;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function login($mail, $pass)
    {
        $postRequest = array(
            'accountType' => 'HOSTED_OR_GOOGLE',
            'Email'       => $mail,
            'Passwd'      => $pass,
            'service'     => "sitemaps",
            'source'      => "Google-WMTdownloadscript-0.11-php"
        );

        $ch = curl_init(self::HOST . '/accounts/ClientLogin');
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => $postRequest
        ));

        $output = curl_exec($ch);
        $info   = curl_getinfo($ch);
        curl_close($ch);

        if (200 != $info['http_code']) {
            throw new Exception('Login failed!');
            exit;
        }
        else {
            @preg_match('/Auth=(.*)/', $output, $match);
            if (isset($match[1])) {
                $this->_auth = $match[1];
                $this->_loggedIn = true;
                return true;
            }
            else {
                throw new Exception('Login failed!');
                exit;
            }
        }
    }

    /**
     * 准备数据
     */
	private function _prepareData()
    {
        if ($this->_loggedIn) {
            $currentIndex = 1;
            $maxResults   = 100;

            $encUri = urlencode($this->_domain);

            /*
             * Get the total result count / result page count
             */
            $feed = $this->_getData("feeds/{$encUri}/crawlissues?start-index=1&max-results=1");
            if (!$feed) {
                return false;
            }

            $doc = new DOMDocument();
            $doc->loadXML($feed);

            $totalResults = (int)$doc->getElementsByTagNameNS('http://a9.com/-/spec/opensearch/1.1/', 'totalResults')->item(0)->nodeValue;
            $resultPages  = (0 != $totalResults) ? ceil($totalResults / $maxResults) : false;

            unset($feed, $doc);

            if (!$resultPages) {
                return false;
            }

            /*
             * Paginate over issue feeds
             */
            else {
                while ($currentIndex <= $resultPages) {
                    $startIndex = ($maxResults * ($currentIndex - 1)) + 1;

                    $feed = $this->_getData("feeds/{$encUri}/crawlissues?start-index={$startIndex}&max-results={$maxResults}");
                    $doc  = new DOMDocument();
                    $doc->loadXML($feed);

                    foreach ($doc->getElementsByTagName('entry') as $node) {
                        $issueType    = $node->getElementsByTagNameNS('http://schemas.google.com/webmasters/tools/2007', 'issue-type')->item(0)->nodeValue;
                        if($issueType == 'not-found') {
                        	$items = $node->getElementsByTagNameNS('http://schemas.google.com/webmasters/tools/2007', 'linked-from');
                        	if($items->length) {
                        		$url          = $node->getElementsByTagNameNS('http://schemas.google.com/webmasters/tools/2007', 'url')->item(0)->nodeValue;
                        		$dateDetected = date('Y-m-d H:i:s', strtotime($node->getElementsByTagNameNS('http://schemas.google.com/webmasters/tools/2007', 'date-detected')->item(0)->nodeValue));
                        		$updated      = date('Y-m-d H:i:s', strtotime($node->getElementsByTagName('updated')->item(0)->nodeValue));
                        
                        		$this->_data[] = $url . "\t\t\tdateDetected:" . $dateDetected . "\tupdated" . $updated ;
                        		foreach($items as $item) {
	                        		$this->_data[] = "\t" . $item->nodeValue;
	                        	}
                        	}
                        }
                    }

                    unset($feed, $doc);
                    $currentIndex++;
                }
                return true;
            }
        }
        else {
            return false;
        }
    }

    private function _getData($url)
    {
        if ($this->_loggedIn) {
            $header = array(
                'Authorization: GoogleLogin auth=' . $this->_auth,
                'GData-Version: 2'
            );

            $ch = curl_init(self::HOST . self::SERVICEURI . $url);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_ENCODING       => 1,
                CURLOPT_HTTPHEADER     => $header
            ));

            $result = curl_exec($ch);
            $info   = curl_getinfo($ch);
            curl_close($ch);

            return (200 != $info['http_code']) ? false : $result;
        }
        else {
            return false;
        }
    }

	private function _HttpHeader() {
        header('Content-type: text/plain; charset=utf-8');
        header('Content-disposition: attachment; filename=' .
            $this->_getFilename());
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    private function _output($localPath = false) {
        $outstream = !$localPath ? 'php://output' : $localPath . DIRECTORY_SEPARATOR . $this->_getFilename();
        $outstream = fopen($outstream, "w");
        fwrite($outstream, implode("\n", $this->_data));
        fclose($outstream);
    }

    private function _getFilename()
    {
        return 'gwt-crawlerrors-' .
            parse_url($this->_domain, PHP_URL_HOST) .'-'.
            date('Ymd-His') . '.txt';
    }

    private function _validateDomain($domain)
    {
        if (!filter_var($domain, FILTER_VALIDATE_URL)) {
            return false;
        }

        $sites = $this->getSites();
        if (!$sites) {
            return false;
        }

        foreach ($sites as $url) {
            if (parse_url($domain, PHP_URL_HOST) == parse_url($url, PHP_URL_HOST)) {
                $this->_domain = $domain;
                return true;
            }
        }

        return false;
    }
}
