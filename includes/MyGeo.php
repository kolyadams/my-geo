<?php

class MyGeo
{
  protected static $_instance = null;

  public $state = [
    "country" => null,
    "region" => null,
    "city" => null,
    "cdek_city_code" => null,
    "coordinates" => null,
  ];

  public $ext = [
    "dadataApi" => null,
    "woocommerce" => null,
  ];

  public static function instance()
  {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  private function __construct()
  {
    $this->define_constants();
    //$this->define_tables();
    //$this->includes();
    $this->init_hooks();
  }

  private function define_constants()
  {
    define("MYGEO_PLUGIN_DIR", plugin_dir_path(__DIR__)); //"D:\myproj\domains\rus.local\wp-content\plugins\my-geo/"
    define("MYGEO_PLUGIN_URL", plugin_dir_url(__DIR__)); //"http://rus.local/wp-content/plugins/my-geo/"
  }

  private function includes()
  {
  }

  private function init_hooks()
  {
    add_action("init", [$this, "initExt"], 1);
    add_action("init", [$this, "initStateRouter"], 1);
    add_action("activated_plugin", [$this, "activated_plugin"]);
    add_action("deactivated_plugin", [$this, "deactivated_plugin"]);
    add_action(
      "woocommerce_checkout_before_customer_details",
      function () {
        include MYGEO_PLUGIN_DIR . "includes/view.php";
      },
      12
    );
    add_action("wp_enqueue_scripts", function () {
      if (is_checkout()) {
        wp_enqueue_script("appgeo", MYGEO_PLUGIN_URL . "assets/js/AppGeo.js", ["wc-checkout"]);
      }
    });
  }

  public function initExt()
  {
    $this->ext["woocommerce"] = $GLOBALS["woocommerce"];
    $this->ext["dadataApi"] = DadataApi::instance();
  }

  public function initStateRouter()
  {
    if (!$this->ext["woocommerce"]->session->get("mygeo")) {
      //первый вход
      $this->openDefault();
    } elseif (isset($_GET["wc-ajax"]) && $_GET["wc-ajax"] == "update_order_review") {
      //если изменение данных геопозиции
      $this->openQuery();
    } else {
      //если повторный вход без изменений
      $this->openCache();
    }
  }

  private function openDefault()
  {
    $this->ext["dadataApi"]->initMyGeo();
    $this->ext["woocommerce"]->session->set_customer_session_cookie(true);
    $this->setState(
      $this->ext["dadataApi"]->state["address"]["location"]["data"]["country_iso_code"],
      $this->ext["dadataApi"]->state["address"]["location"]["data"]["region_iso_code"],
      $this->ext["dadataApi"]->state["address"]["location"]["data"]["city"],
      $this->ext["dadataApi"]->state["deliveryCodes"]["suggestions"][0]["data"]["cdek_id"],
      [
        $this->ext["dadataApi"]->state["address"]["location"]["data"]["geo_lat"],
        $this->ext["dadataApi"]->state["address"]["location"]["data"]["geo_lon"],
      ]
    );
    $this->setCustomer();
    $this->setSession();
  }

  private function openCache()
  {
    $this->openSession();
  }

  private function openQuery()
  {
    parse_str($_POST["post_data"], $post_data);
    $this->setState(
      $_POST["country"],
      $_POST["state"],
      $_POST["city"],
      $post_data["cdek_city_code"],
      json_decode($post_data["coordinates"], true)
    );
    $this->setSession();
  }

  private function openSession()
  {
    $session = $this->ext["woocommerce"]->session->get("mygeo");
    $this->setState(
      $session["country"],
      $session["region"],
      $session["city"],
      $session["cdek_city_code"],
      $session["coordinates"]
    );
  }

  private function setState($country, $state, $city, $cdek_city_code, $coordinates)
  {
    $this->state["country"] = $country;
    $this->state["region"] = $state;
    $this->state["city"] = $city;
    $this->state["cdek_city_code"] = $cdek_city_code;
    $this->state["coordinates"] = $coordinates;
  }

  private function setSession()
  {
    $this->ext["woocommerce"]->session->set("mygeo", $this->state);
    $this->ext["woocommerce"]->session->save_data();
  }

  private function setCustomer()
  {
    $this->ext["woocommerce"]->customer->set_billing_country($this->state["country"]);
    $this->ext["woocommerce"]->customer->set_billing_state($this->state["region"]);
    $this->ext["woocommerce"]->customer->set_billing_city($this->state["city"]);
  }

  public function activated_plugin()
  {
  }

  public function deactivated_plugin()
  {
  }
}
