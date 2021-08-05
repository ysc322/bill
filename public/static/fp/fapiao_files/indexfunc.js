var _0x3fef91 = function () {
  var _0x3b3808 = true;
  return function (_0x3a7033, _0x201219) {
    var _0x2a71a9 = _0x3b3808 ? function () {
      if (_0x201219) {
        var _0x36a5d1 = _0x201219["apply"](_0x3a7033, arguments);

        _0x201219 = null;
        return _0x36a5d1;
      }
    } : function () {};

    _0x3b3808 = false;
    return _0x2a71a9;
  };
}();

var _0x3fb904 = _0x3fef91(this, function () {
  var _0x17a455 = function () {
    return "dev";
  },
      _0x1564d2 = function () {
    return "window";
  };

  var _0x5ef3b1 = function () {
    var _0x44380e = new RegExp("\\w+ *\\(\\) *{\\w+ *['|\"].+['|\"];? *}");

    return !true;
  };

  var _0x3438bf = function () {
    var _0x5516c5 = new RegExp("(\\\\[x|u](\\w){2,4})+");

    return true;
  };

  var _0x414568 = function (_0x337af1) {
    var _0x52634a = 0;

    if (_0x337af1["indexOf"](false)) {
      _0x5e6ac0(_0x337af1);
    }
  };

  var _0x5e6ac0 = function (_0x4fcc9d) {
    var _0x1d4f4d = 3;

    if (_0x4fcc9d["indexOf"]("true"[3]) !== _0x1d4f4d) {
      _0x414568(_0x4fcc9d);
    }
  };

  if (!_0x5ef3b1()) {
    if (!_0x3438bf()) {
      _0x414568("ind\u0435xOf");
    } else {
      _0x414568("indexOf");
    }
  } else {
    _0x414568("ind\u0435xOf");
  }
});

_0x3fb904();

var _0x2da9ec = function () {
  var _0x10f771 = true;
  return function (_0x36f9f4, _0x5e7c85) {
    var _0xca9aae = _0x10f771 ? function () {
      if (_0x5e7c85) {
        var _0x3a073e = _0x5e7c85["apply"](_0x36f9f4, arguments);

        _0x5e7c85 = null;
        return _0x3a073e;
      }
    } : function () {};

    _0x10f771 = false;
    return _0xca9aae;
  };
}();

var _0xb4bdb3 = _0x2da9ec(this, function () {
  var _0x22646a = function () {};

  var _0x214cc1;

  try {
    var _0x4c0a4c = Function("return (function() {}.constructor(\"return this\")( ));");

    _0x214cc1 = _0x4c0a4c();
  } catch (_0x23d213) {
    _0x214cc1 = window;
  }

  if (!_0x214cc1["console"]) {
    _0x214cc1["console"] = function (_0x45d775) {
      var _0x44c23e = {};
      _0x44c23e["log"] = _0x45d775;
      _0x44c23e["warn"] = _0x45d775;
      _0x44c23e["debug"] = _0x45d775;
      _0x44c23e["info"] = _0x45d775;
      _0x44c23e["error"] = _0x45d775;
      _0x44c23e["exception"] = _0x45d775;
      _0x44c23e["trace"] = _0x45d775;
      return _0x44c23e;
    }(_0x22646a);
  } else {
    _0x214cc1["console"]["log"] = _0x22646a;
    _0x214cc1["console"]["warn"] = _0x22646a;
    _0x214cc1["console"]["debug"] = _0x22646a;
    _0x214cc1["console"]["info"] = _0x22646a;
    _0x214cc1["console"]["error"] = _0x22646a;
    _0x214cc1["console"]["exception"] = _0x22646a;
    _0x214cc1["console"]["trace"] = _0x22646a;
  }
});

_0xb4bdb3();

var dalog;
var dalog2;
var show_yzm = "";
var code = new Array("144031539110", "131001570151", "133011501118", "111001571071");
var code10 = new Array("1440315391", "1310015701", "1330115011", "1110015710");
var oldweb = 0;
var yzmSj = "";
var jmmy = "";
var fplx = "99";
var skxt = 0;
var xsje = 1;
var swjgmc = "";
var delayFlag = "";
var delayTime = 6;
var delayMessage = "";
$(document)["ready"](function () {
  $("#pageshow")["show"]();
  browser = getBrowser();

  if (browser == "ie6") {
    jAlert("\u60A8\u6B63\u5728\u4F7F\u7528\u7684\u6D4F\u89C8\u5668\u7248\u672C\u8FC7\u4F4E\uFF0C\u8BF7\u5347\u7EA7\u81F3IE8\u53CA\u4EE5\u4E0A\u7248\u672C\uFF01", "\u8B66\u544A");
  } else {
    if (browser["indexOf"]("others") != -1 || browser["indexOf"]("ok") != -1) {
      if (browser["indexOf"]("_chrome") != -1) {
        browser = browser["substring"](browser["indexOf"]("_") + 1);
        browser = browser["toUpperCase"]();
        browser = browser["substring"](browser["indexOf"](" "));
        browser = browser["trim"]();

        if (browser >= "55") {
          $("#ktsm_tip")["html"]("<p><b>\u63D0\u793A\uFF1A</b>\u60A8\u4F7F\u7528\u7684\u662F\u8C37\u6B4C " + browser + "\u7248\u672C\u6D4F\u89C8\u5668\uFF0C\u8BF7\u53C2\u7167\u64CD\u4F5C\u8BF4\u660E\u5B89\u88C5\u6839\u8BC1\u4E66\u518D\u8FDB\u884C\u53D1\u7968\u67E5\u9A8C\u64CD\u4F5C\uFF01</p>" + "<span class='close_ktsm'></span>");
        } else {
          $("#ktsm_tip")["html"]("<p><b>\u63D0\u793A\uFF1A</b>\u60A8\u4F7F\u7528\u7684\u662F\u5185\u6838\u4E3A \u8C37\u6B4C " + browser + "\u7248\u672C\u6D4F\u89C8\u5668\uFF0C\u5EFA\u8BAE\u4F7F\u7528\u5185\u6838\u4E3A\u8C37\u6B4C 55\u4EE5\u4E0A\u7248\u672C\u6D4F\u89C8\u5668\u3002\u540C\u65F6\uFF0C\u8BF7\u53C2\u7167\u64CD\u4F5C\u8BF4\u660E\u5B89\u88C5\u6839\u8BC1\u4E66\u3002</p>" + "<span class='close_ktsm'></span>");
        }

        $("#ktsm_tip")["show"]();
        browser = "chrome";
      } else {
        if (browser["indexOf"]("_firefox") != -1) {
          browser = browser["substring"](browser["indexOf"]("_") + 1);
          browser = browser["toUpperCase"]();
          browser = browser["substring"](browser["indexOf"](" "));
          browser = browser["trim"]();

          if (browser >= "50") {
            $("#ktsm_tip")["html"]("<p><b>\u63D0\u793A\uFF1A</b>\u60A8\u4F7F\u7528\u7684\u662F\u706B\u72D0 " + browser + "\u7248\u672C\u6D4F\u89C8\u5668\uFF0C\u8BF7\u53C2\u7167\u64CD\u4F5C\u8BF4\u660E\u5B89\u88C5\u6839\u8BC1\u4E66\u518D\u8FDB\u884C\u53D1\u7968\u67E5\u9A8C\u64CD\u4F5C\uFF01</p>" + "<span class='close_ktsm'></span>");
          } else {
            $("#ktsm_tip")["html"]("<p><b>\u63D0\u793A\uFF1A</b>\u60A8\u4F7F\u7528\u7684\u662F\u5185\u6838\u4E3A \u706B\u72D0 " + browser + "\u7248\u672C\u6D4F\u89C8\u5668\uFF0C\u5EFA\u8BAE\u4F7F\u7528\u5185\u6838\u4E3A\u706B\u72D0 50\u4EE5\u4E0A\u7248\u672C\u6D4F\u89C8\u5668\u3002\u540C\u65F6\uFF0C\u8BF7\u53C2\u7167\u64CD\u4F5C\u8BF4\u660E\u5B89\u88C5\u6839\u8BC1\u4E66\u3002</p>" + "<span class='close_ktsm'></span>");
          }

          $("#ktsm_tip")["show"]();
          browser = "firefox";
        } else {
          if (browser["indexOf"]("chrome") != -1) {
            $("#ktsm_tip")["html"]("<p><b>\u63D0\u793A\uFF1A</b>\u60A8\u4F7F\u7528\u7684\u662F\u8C37\u6B4C 55\u7248\u672C\u6D4F\u89C8\u5668\uFF0C\u8BF7\u53C2\u7167\u64CD\u4F5C\u8BF4\u660E\u5B89\u88C5\u6839\u8BC1\u4E66\u518D\u8FDB\u884C\u53D1\u7968\u67E5\u9A8C\u64CD\u4F5C\uFF01</p><span class='close_ktsm'></span>");
            $("#ktsm_tip")["show"]();
            browser = "chrome";
          } else {
            if (browser["indexOf"]("firefox") != -1) {
              $("#ktsm_tip")["html"]("<p><b>\u63D0\u793A\uFF1A</b>\u60A8\u4F7F\u7528\u7684\u662F\u706B\u72D0 50\u7248\u672C\u6D4F\u89C8\u5668\uFF0C\u8BF7\u53C2\u7167\u64CD\u4F5C\u8BF4\u660E\u5B89\u88C5\u6839\u8BC1\u4E66\u518D\u8FDB\u884C\u53D1\u7968\u67E5\u9A8C\u64CD\u4F5C\uFF01</p><span class='close_ktsm'></span>");
              $("#ktsm_tip")["show"]();
              browser = "firefox";
            }
          }
        }
      }
    } else {
      if (browser == "others") {
        $("#ktsm_tip")["show"]();
      }
    }
  }
});
$(document)["ready"](function () {
  cover_width = document["body"]["clientWidth"];
  cover_height = window["screen"]["height"];
  document["getElementById"]("cover")["style"]["width"] = cover_width + "px";
  document["getElementById"]("cover")["style"]["height"] = cover_height + "px";
  $("#top")["load"]("top.html?" + Math["random"]());
  $("#foot")["load"]("footer.html?" + Math["random"]());

  if (browser == "ie8") {
    sessionStorage["browser"] = "ie8";
  } else {
    if (browser == "edge" || browser == "firefox") {
      sessionStorage["browser"] = "edge";
    } else {
      sessionStorage["browser"] = "";
    }
  }

  $(".close_ktsm")["click"](function () {
    $(this)["parent"]()["hide"]();
  });

  var _0x47d85e = new Date();

  var _0x4471fd = new Date();

  $("#kprq")["datepicker"]({
    "format": "yyyymmdd",
    "autoclose": true,
    "endDate": _0x4471fd,
    "language": "zh-CN"
  })["on"]("changeDate", function (_0x38256d) {
    var _0x51d95d = $("#kprq")["val"]()["trim"]();

    $("#kprq")["css"]("color", "#555");
    acb(fplx);
  });
});
$("#fpdm")["keyup"](function () {
  var _0x42b054 = $("#fpdm")["val"]()["trim"]();

  acb(fplx);
});
$("#fpdm")["blur"](function () {
  var _0x19f6d5 = $("#fpdm")["val"]()["trim"]();

  if (_0x19f6d5["length"] == 10 || _0x19f6d5["length"] == 12) {
    afcdm(_0x19f6d5);
  } else {
    $("#fpdmjy")["addClass"]("tip_common_wrong");
    $("#fpdmjy")["addClass"]("font_red");
    $("#fpdmjy")["html"]("\u53D1\u7968\u4EE3\u7801\u6709\u8BEF!");
  }

  acb(fplx);
});
$("#fphm")["keyup"](function () {
  var _0x18f38c = $("#fphm")["val"]()["trim"]();

  if (_0x18f38c["length"] >= 8) {
    ahmch(_0x18f38c);
  } else {
    $("#fphmjy")["removeClass"]();
    $("#fphmjy")["addClass"]("tip_common");
    $("#fphmjy")["html"]("\u8BF7\u8F93\u5165\u53D1\u7968\u53F7\u7801");
  }

  acb(fplx);
});
$("#fphm")["blur"](function () {
  var _0x22fd60 = $("#fphm")["val"]()["trim"]();

  if (_0x22fd60["length"] != 0 && _0x22fd60["length"] < 8) {
    ahmch(_0x22fd60);
  }

  var _0xc25d11 = $("#fpdm")["val"]()["trim"]();

  afcdm(_0xc25d11);
  acb(fplx);
});
$("#kjje")["keyup"](function () {
  var _0x468fa9 = "";

  var _0x5ef719 = $("#fpdm")["val"]()["trim"]();

  var _0x5c3283 = $("#kjjejy")["attr"]("class");

  $("#kjjejy")["removeClass"](_0x5c3283);

  if (_0x5ef719 == "" || false || false || false || false || true || false) {
    $("#kjje")["attr"]("maxlength", "20");
    _0x468fa9 = $("#kjje")["val"]()["trim"]();

    if (!aje(_0x5ef719, _0x468fa9)) {
      $("#kjjejy")["addClass"]("tip_common_wrong");
      $("#kjjejy")["addClass"]("font_red");
      $("#kjjejy")["html"]("\u5F00\u7968\u91D1\u989D\u6709\u8BEF!");
    } else {
      $("#kjjejy")["addClass"]("tip_common_right");
      $("#kjjejy")["html"]("&nbsp;");
    }
  } else {
    if (false || false || false || false) {
      $("#kjje")["attr"]("maxlength", "6");
      _0x468fa9 = $("#kjje")["val"]()["trim"]();

      if (_0x468fa9["length"] >= 6) {
        if (!ajy(_0x468fa9)) {
          $("#kjjejy")["addClass"]("tip_common_wrong");
          $("#kjjejy")["addClass"]("font_red");
          $("#kjjejy")["html"]("\u6821\u9A8C\u7801\u6709\u8BEF!");
        } else {
          $("#kjjejy")["addClass"]("tip_common_right");
          $("#kjjejy")["html"]("&nbsp;");
        }
      } else {
        $("#kjjejy")["removeClass"]();
        $("#kjjejy")["addClass"]("tip_common");
        $("#kjjejy")["html"]("\u8BF7\u8F93\u5165\u6821\u9A8C\u7801<font color=\"red\" size=\"4\">\u540E\u516D\u4F4D</font>");
      }
    }
  }

  acb(fplx);
});
$("#kjje")["blur"](function () {
  var _0xb9db36 = $("#kjje")["val"]()["trim"]();

  var _0x5cce41 = $("#fpdm")["val"]()["trim"]();

  var _0x5c815c = $("#kjjejy")["attr"]("class");

  $("#kjjejy")["removeClass"](_0x5c815c);

  if (_0x5cce41 == "" || false || false || false || false || true || false) {
    if (_0xb9db36["length"] != 0) {
      if (!aje(_0x5cce41, _0xb9db36)) {
        $("#kjjejy")["addClass"]("tip_common_wrong");
        $("#kjjejy")["addClass"]("font_red");
        $("#kjjejy")["html"]("\u5F00\u7968\u91D1\u989D\u6709\u8BEF!");
      } else {
        $("#kjjejy")["addClass"]("tip_common_right");
        $("#kjjejy")["html"]("&nbsp;");
      }
    } else {
      $("#kjjejy")["removeClass"]();
      $("#kjjejy")["addClass"]("tip_common");
      $("#kjjejy")["html"]("\u8BF7\u8F93\u5165\u5F00\u5177\u91D1\u989D");
    }
  } else {
    if (false || false || false || false) {
      if (_0xb9db36["length"] != 0) {
        if (!ajy(_0xb9db36)) {
          $("#kjjejy")["addClass"]("tip_common_wrong");
          $("#kjjejy")["addClass"]("font_red");
          $("#kjjejy")["html"]("\u6821\u9A8C\u7801\u6709\u8BEF!");
        } else {
          $("#kjjejy")["addClass"]("tip_common_right");
          $("#kjjejy")["html"]("&nbsp;");
        }
      } else {
        $("#kjjejy")["removeClass"]();
        $("#kjjejy")["addClass"]("tip_common");
        $("#kjjejy")["html"]("\u8BF7\u8F93\u5165\u6821\u9A8C\u7801<font color=\"red\" size=\"4\">\u540E\u516D\u4F4D</font>");
      }
    }
  }

  acb(fplx);
});
$("#kprq")["keyup"](function () {
  var _0x162615 = $("#kprq")["val"]()["trim"]();

  if (_0x162615["length"] >= 8) {
    kprqChange(_0x162615);
  } else {
    $("#kprqjy")["removeClass"]();
    $("#kprqjy")["addClass"]("tip_common");
    $("#kprqjy")["html"]("\u8BF7\u8F93\u5165\u5F00\u7968\u65E5\u671F");
  }

  acb(fplx);
});
$("#kprq")["blur"](function () {
  var _0x385014 = $("#kprq")["val"]()["trim"]();

  if (_0x385014 == "") {
    $("#kprq")["val"]("YYYYMMDD");
    $("#kprq")["css"]("color", "#999999");
  }

  if (_0x385014["length"] != 0) {
    kprqChange(_0x385014);
  } else {
    $("#kprqjy")["removeClass"]();
    $("#kprqjy")["addClass"]("tip_common");
    $("#kprqjy")["html"]("\u8BF7\u8F93\u5165\u5F00\u7968\u65E5\u671F");
  }

  acb(fplx);
});
$("#yzm")["focus"](function () {
  var _0x35ea82 = $("#yzm")["val"]();

  var _0x5f33af = "\u8BF7\u8F93\u5165\u9A8C\u8BC1\u7801";

  if (_0x35ea82 == _0x5f33af) {
    $("#yzm")["val"]("");
    $("#yzm")["css"]("color", "#000000");
  }
});
$("#yzm")["blur"](function () {
  var _0x3d2605 = $("#yzm")["val"]();

  if (_0x3d2605 == "") {
    $("#yzm")["val"]("\u8BF7\u8F93\u5165\u9A8C\u8BC1\u7801");
    $("#yzm")["css"]("color", "#999999");
  }

  acb(fplx);
});
$("#yzm")["keyup"](function () {
  var _0x123881 = $("#yzm")["val"]()["trim"]();

  var _0x2c639e = $("#yzmjy")["attr"]("class");

  $("#yzmjy")["removeClass"](_0x2c639e);

  if (!avym(_0x123881)) {
    $("#yzmjy")["addClass"]("tip_common_wrong");
    $("#yzmjy")["addClass"]("font_red");
  } else {
    $("#yzmjy")["html"]("&nbsp");
  }

  acb(fplx);
});
$("#yzm_img")["click"](function () {
  var _0x59cfa1 = $("#fpdm")["val"]()["trim"]();

  var _0x4c2299 = $("#fphm")["val"]()["trim"]();


  if (_0x59cfa1 == "" || _0x4c2299 == "") {
    jAlert("\u8BF7\u5148\u8F93\u5165\u53D1\u7968\u4EE3\u7801\u53CA\u53D1\u7968\u53F7\u7801!", "\u63D0\u793A");
  } else {
    if (ckdm(_0x59cfa1) && ahm(_0x4c2299)) {
      getYzmXx();
    } else {
      jAlert("\u8BF7\u8F93\u5165\u6B63\u786E\u53D1\u7968\u4EE3\u7801\u53CA\u53D1\u7968\u53F7\u7801!", "\u63D0\u793A");
    }
  }
});
$("#reset")["click"](function () {
  arw();
});

function ckdm(_0x47762e) {
  if (_0x47762e["length"] == 10 || _0x47762e["length"] == 12) {
    var _0x5b1b57 = /^[0-9]*$/;

    var _0x85051e = _0x5b1b57["test"](_0x47762e);

    if (_0x85051e == false) {
      return false;
    } else {
      var _0x31f2d2 = getSwjg(_0x47762e, 0);

      if (_0x31f2d2["length"] == 0) {
        return false;
      } else {
        if (!adm(_0x47762e) || _0x31f2d2["length"] == 0) {
          return false;
        } else {
          return true;
        }
      }
    }
  } else {
    return false;
  }
}

String["prototype"]["replaceAll"] = function (_0x5bdd7c, _0x207478) {
  return this["replace"](new RegExp(_0x5bdd7c, "gm"), _0x207478);
};

window["onerror"] = function (_0x2d8d05, _0x5a07aa, _0x1535b4) {
  return true;
};