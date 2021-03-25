<?php
namespace MeatNightApi\Adapter\Beecomm;

interface Settings
{
	public function connect(...$fields);//string $client_id, string $client_secret
}

interface Options
{
	public function GetAuthorized();

	public function set_host(string $host);
	public function get_host();

	public function set_client_id(string $client_id);
	public function get_client_id();

	public function set_client_secret(string $client_secret);
	public function get_client_secret();

	public function set_customer_info(...$info);
}

class BeecommApi implements Options
{
	private $host;
	private $client_id;
	private $client_secret;
	private $access_token;

	private $customer;
	private $branchId;
	private $customerName;

	public function __construct(string $client_id, string $client_secret)
	{
		$this->host = self::get_host();

		self::set_client_id($client_id);
		self::set_client_secret($client_secret);
		self::GetAuthorized();
	}

	public function set_client_secret(string $client_secret): string {
		return $this->client_secret = $client_secret;
	}

	public function get_client_secret(): string {
		return $this->client_secret;
	}

	public function set_client_id(string $client_id): string {
		return $this->client_id = $client_id;
	}

	public function get_client_id(): string {
		return $this->client_id;
	}

	public function set_customer_info(...$info): void {
		$this->customer     = $info['customer'];
		$this->branchId     = $info['branchId'];
		$this->customerName = $info['customerName'];
	}

	public function get_access_token(): string {
		return $this->access_token;
	}

	public function set_access_token(string $access_token): string {
		return $this->access_token = $access_token;
	}

	public function get_host(): string {
		return $this->host;
	}

	public function set_host(string $host): void {
		$this->host = $host;
	}

	/**
	 * Set Token Access by GetAuthorized method from Api
	 */
	public function GetAuthorized(): void//string $client_id, string $client_secret
	{
		$fields = [self::get_client_id(), self::get_client_secret()];
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => $this->host,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => http_build_query($fields),
			CURLOPT_HTTPHEADER => [
				//"content-type: application/x-www-form-urlencoded"
				'Content-Type: application/json',
				'Accept: application/json'
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			$data = json_decode($response, true);
			self::set_access_token($data['access_token']);
		}
	}

	/**
	 * Get Customer by token
	 */
	public function GetCustomers(): void
	{
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => $this->host,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_HTTPHEADER => [
				//"content-type: application/x-www-form-urlencoded"
				'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
				'x-access-token:'.self::get_access_token()
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			$data = json_decode($response, true);
			self::set_customer_info($data['customers'][0]);
		}
	}
}