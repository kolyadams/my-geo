import React from "react";
import ReactDOM from "react-dom";
import "./main.scss";

let myGeo = {
  state: {
    suggestions: null,
    deliverycodes: null,
  },
  delayTimer: null,
  root: null,
  init() {
    this.events();
    this.renderRoot();
  },
  events() {
    document.querySelector("body").addEventListener("keydown", this.keydown, true);
    document.querySelector("#billing_city").addEventListener("input", this.expandSuggestions);
    document.querySelector("body").addEventListener("click", this.setForm);
  },

  renderRoot() {
    let root = document.createElement("div");
    root.className = "mygeo-search__root";
    document.querySelector("#billing_city_field").append(root);
    this.root = root;
  },

  expandSuggestions(e) {
    clearTimeout(myGeo.delayTimer);
    myGeo.delayTimer = setTimeout(async function () {
      let response = await fetch(`/?action=getSuggestions&sugQuery=${e.target.value}`);
      myGeo.state.suggestions = await response.json();
      let list = [];
      myGeo.state.suggestions.suggestions.forEach(function (value, index) {
        list.push(
          <div key={index} className="mygeo-search__item" data-index={index}>
            {value.value}
          </div>
        );
      });
      ReactDOM.render(<>{list}</>, myGeo.root);
      myGeo.root.classList.add("is-active");
    }, 500);
  },

  keydown(e) {
    if ((e.target.id = "billing_city")) {
      e.stopPropagation();
    }
  },

  async getDeliveryCodes(kladr) {
    let response = await fetch(`/?action=getDeliveryCodes&kladr=${kladr}`);
    this.state.deliverycodes = await response.json();
  },

  async setForm(e) {
    if (e.target && e.target.className == "mygeo-search__item") {
      let index = e.target.getAttribute("data-index");
      let data = myGeo.state.suggestions.suggestions[index].data;
      await myGeo.getDeliveryCodes(data.city_kladr_id);
      jQuery("#billing_country").val(data.country_iso_code).trigger("change"); //страна
      jQuery("#billing_state").val(data.region_iso_code).trigger("change"); //регион
      document.querySelector("#billing_city").value = data.city; //город
      document.querySelector(".my-geo__coordinates").value = `[${data.geo_lat},${data.geo_lon}]`; //coordinates
      document.querySelector(".my-geo__cdek_city_code").value =
        myGeo.state.deliverycodes["suggestions"][0]["data"]["cdek_id"]; //cdek_city_code
      document.querySelector(".mygeo-search__root").classList.remove("is-active"); //прячем список городов
      jQuery("body").trigger("update_checkout"); //вызываем пересчет цен доставки
    }
  },
};

jQuery(function () {
  myGeo.init();
});

/*
this.bind.keydown = this.keydown.bind(this);
document.querySelector("body").addEventListener("keydown", this.bind.keydown, true);
document.querySelector("body").removeEventListener("keydown", this.bind.keydown, true);*/
