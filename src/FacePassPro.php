<?php

namespace Yauhenko\Anviz;

use Exception;

/**
 * Class FacePassPro
 *
 * @package Yauhenko\Anviz
 */
class FacePassPro {

	/**
	 * FacePassPro IP address
	 *
	 * @var string
	 */
	protected $ip;

	/**
	 * FacePassPro admin password
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * FacePassPro constructor
	 *
	 * @param string $ip
	 * @param string $password
	 */
	public function __construct(string $ip, string $password) {
		$this->ip = $ip;
		$this->password = $password;
	}

	/**
	 * Get Identification Records
	 * Arguments $from and $till parsed via strtotime()
	 *
	 * @see https://www.php.net/manual/en/function.strtotime.php
	 * @param string $from
	 * @param string|null $till
	 * @param int|null $id
	 * @param string|null $cardno
	 * @return array
	 * @throws Exception
	 */
	public function getIdentificationRecords(?string $from = 'today', ?string $till = null, ?int $id = null, ?string $cardno = null): array {
		if(!$from) $from = 'today';
		if(!$till) $till = $from;
		$params = [
			'startday' => date('m-d-Y', strtotime($from)),
			'endday' => date('m-d-Y', strtotime($till)),
			'username' => '',
			'userno' => $id,
			'st' => '-1',
			'cardno' => $cardno,
			'save' => 'Inquiry'
		];
		$data = $this->fetch("http://{$this->ip}/records.asp", $params);
		preg_match('/(sd_records[0-9]+\.csv)/', $data, $m);
		$records = [];
		if(!$m[1]) return $records;
		$data = $this->fetch("http://{$this->ip}/{$m[1]}");
		$keys = [];
		foreach (explode("\n", $data) as $line) {
			if(!$keys) {
				$keys = explode(',', str_replace(' ', '_', strtolower(trim($line))));
				if(!$keys[0]) break;
				continue;
			}
			$values = explode(',', trim($line));
			if(!$values[0]) break;
			$record = array_combine($keys, $values);
			$d = explode('-', $record['date']);
			$record['date'] = "{$d[2]}-{$d[0]}-{$d[1]}";
			$record['status'] = strtolower($record['status']);
			unset($record['no']);
			$records[] = $record;
		}
		return $records;
	}

	/**
	 * @param $url
	 * @param array|null $data
	 * @return string
	 * @throws Exception
	 */
	protected function fetch($url, array $data = null): string {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_USERPWD, "admin:{$this->password}");
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		if ($data) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$res = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if(!$code) throw new Exception('Connection error');
		elseif($code === 401) throw new Exception('Invalid password');
		elseif($code !== 200) throw new Exception("Failed to fetch! HTTP-code {$code}");
		elseif(!$res) throw new Exception("Empty response");
		curl_close($ch);
		return $res;
	}

}
