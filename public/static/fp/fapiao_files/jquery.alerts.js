String["prototype"]["trim"] = function () {
  return this["replace"](/(^\s*)|(\s*$)/g, "");
};

(function (_0x1e7c95) {
  _0x1e7c95["alerts"] = {
    "verticalOffset": -75,
    "horizontalOffset": 0,
    "repositionOnResize": true,
    "overlayOpacity": 0.7,
    "overlayColor": "#000",
    "draggable": false,
    "okButton": "&nbsp;\u786E\u5B9A&nbsp;",
    "cancelButton": "&nbsp;\u53D6\u6D88&nbsp;",
    "dialogClass": null,
    "alert": function (_0x28b48a, _0x5287ef, _0x18f32b) {
      if (_0x5287ef == null) {
        _0x5287ef = "Alert";
      }

      _0x1e7c95["alerts"]["_show"](_0x5287ef, _0x28b48a, null, "alert", function (_0x3bb677) {
        if (_0x18f32b) {
          _0x18f32b(_0x3bb677);
        }
      });
    },
    "confirm": function (_0x991bf7, _0x29d730, _0x23aa6d) {
      if (_0x29d730 == null) {
        _0x29d730 = "Confirm";
      }

      _0x1e7c95["alerts"]["_show"](_0x29d730, _0x991bf7, null, "confirm", function (_0x12deb8) {
        if (_0x23aa6d) {
          _0x23aa6d(_0x12deb8);
        }
      });
    },
    "prompt": function (_0x120b85, _0x508538, _0x3f8b15, _0x2bc424) {
      if (_0x3f8b15 == null) {
        _0x3f8b15 = "Prompt";
      }

      _0x1e7c95["alerts"]["_show"](_0x3f8b15, _0x120b85, _0x508538, "prompt", function (_0x485853) {
        if (_0x2bc424) {
          _0x2bc424(_0x485853);
        }
      });
    },
    "_show": function (_0x54c37b, _0xcb9724, _0x1102a9, _0x3e1dc7, _0x3f4a13) {
      _0x1e7c95["alerts"]["_hide"]();

      _0x1e7c95["alerts"]["_overlay"]("show");

      _0x1e7c95("BODY")["append"]("<div id=\"popup_container\"><h1 id=\"popup_title\"></h1><div id=\"popup_content\"><div id=\"popup_message\"></div></div></div>");

      if (_0x1e7c95["alerts"]["dialogClass"]) {
        _0x1e7c95("#popup_container")["addClass"](_0x1e7c95["alerts"]["dialogClass"]);
      }

      var _0x1bab23 = "fixed";

      _0x1e7c95("#popup_container")["css"]({
        "position": _0x1bab23,
        "zIndex": 99999,
        "padding": 0,
        "margin": 0
      });

      _0x1e7c95("#popup_title")["text"](_0x54c37b);

      _0x1e7c95("#popup_content")["addClass"](_0x3e1dc7);

      _0x1e7c95("#popup_message")["text"](_0xcb9724);

      _0x1e7c95("#popup_message")["html"](_0x1e7c95("#popup_message")["text"]()["replace"](/\n/g, "<br />"));

      _0x1e7c95("#popup_container")["css"]({
        "minWidth": _0x1e7c95("#popup_container")["outerWidth"](),
        "maxWidth": _0x1e7c95("#popup_container")["outerWidth"]()
      });

      _0x1e7c95["alerts"]["_reposition"]();

      _0x1e7c95["alerts"]["_maintainPosition"](true);

      switch (_0x3e1dc7) {
        case "alert":
          _0x1e7c95("#popup_message")["after"]("<div id=\"popup_panel\"><input type=\"button\" value=\"" + _0x1e7c95["alerts"]["okButton"] + "\" id=\"popup_ok\" class=\"bluebtn\"/></div>");

          _0x1e7c95("#popup_ok")["click"](function () {
            _0x1e7c95["alerts"]["_hide"]();

            _0x3f4a13(true);
          });

          _0x1e7c95("#popup_ok")["focus"]()["keypress"](function (_0x20ba34) {
            if (_0x20ba34["keyCode"] == 13 || _0x20ba34["keyCode"] == 27) {
              _0x1e7c95("#popup_ok")["trigger"]("click");
            }
          });

          break;

        case "confirm":
          _0x1e7c95("#popup_message")["after"]("<div id=\"popup_panel\"><input type=\"button\" value=\"" + _0x1e7c95["alerts"]["okButton"] + "\" id=\"popup_ok\" class=\"bluebtn\"/> <input type=\"button\" value=\"" + _0x1e7c95["alerts"]["cancelButton"] + "\" id=\"popup_cancel\" class=\"bluebtn\"/></div>");

          _0x1e7c95("#popup_ok")["click"](function () {
            _0x1e7c95["alerts"]["_hide"]();

            if (_0x3f4a13) {
              _0x3f4a13(true);
            }
          });

          _0x1e7c95("#popup_cancel")["click"](function () {
            _0x1e7c95["alerts"]["_hide"]();

            if (_0x3f4a13) {
              _0x3f4a13(false);
            }
          });

          _0x1e7c95("#popup_ok")["focus"]();

          _0x1e7c95("#popup_ok, #popup_cancel")["keypress"](function (_0x40ce4a) {
            if (_0x40ce4a["keyCode"] == 13) {
              _0x1e7c95("#popup_ok")["trigger"]("click");
            }

            if (_0x40ce4a["keyCode"] == 27) {
              _0x1e7c95("#popup_cancel")["trigger"]("click");
            }
          });

          break;

        case "prompt":
          _0x1e7c95("#popup_message")["append"]("<br /><input type=\"text\" size=\"30\" id=\"popup_prompt\" />")["after"]("<div id=\"popup_panel\"><input type=\"button\" value=\"" + _0x1e7c95["alerts"]["okButton"] + "\" id=\"popup_ok\" /> <input type=\"button\" value=\"" + _0x1e7c95["alerts"]["cancelButton"] + "\" id=\"popup_cancel\" /></div>");

          _0x1e7c95("#popup_prompt")["width"](_0x1e7c95("#popup_message")["width"]());

          _0x1e7c95("#popup_ok")["click"](function () {
            var _0xf33486 = _0x1e7c95("#popup_prompt")["val"]();

            _0x1e7c95["alerts"]["_hide"]();

            if (_0x3f4a13) {
              _0x3f4a13(_0xf33486);
            }
          });

          _0x1e7c95("#popup_cancel")["click"](function () {
            _0x1e7c95["alerts"]["_hide"]();

            if (_0x3f4a13) {
              _0x3f4a13(null);
            }
          });

          _0x1e7c95("#popup_prompt, #popup_ok, #popup_cancel")["keypress"](function (_0x466772) {
            if (_0x466772["keyCode"] == 13) {
              _0x1e7c95("#popup_ok")["trigger"]("click");
            }

            if (_0x466772["keyCode"] == 27) {
              _0x1e7c95("#popup_cancel")["trigger"]("click");
            }
          });

          if (_0x1102a9) {
            _0x1e7c95("#popup_prompt")["val"](_0x1102a9);
          }

          _0x1e7c95("#popup_prompt")["focus"]()["select"]();

          break;
      }

      if (_0x1e7c95["alerts"]["draggable"]) {
        try {
          _0x1e7c95("#popup_container")["draggable"]({
            "handle": _0x1e7c95("#popup_title")
          });

          _0x1e7c95("#popup_title")["css"]({
            "cursor": "move"
          });
        } catch (_0x44473e) {}
      }
    },
    "_hide": function () {
      _0x1e7c95("#popup_container")["remove"]();

      _0x1e7c95["alerts"]["_overlay"]("hide");

      _0x1e7c95["alerts"]["_maintainPosition"](false);
    },
    "_overlay": function (_0x57d546) {
      switch (_0x57d546) {
        case "show":
          _0x1e7c95["alerts"]["_overlay"]("hide");

          _0x1e7c95("BODY")["append"]("<div id=\"popup_overlay\"></div>");

          _0x1e7c95("#popup_overlay")["css"]({
            "position": "absolute",
            "zIndex": 99998,
            "top": "0px",
            "left": "0px",
            "width": "100%",
            "height": _0x1e7c95(document)["height"](),
            "background": _0x1e7c95["alerts"]["overlayColor"],
            "opacity": _0x1e7c95["alerts"]["overlayOpacity"]
          });

          break;

        case "hide":
          _0x1e7c95("#popup_overlay")["remove"]();

          break;
      }
    },
    "_reposition": function () {
      var _0x29a8b9 = _0x1e7c95(window)["height"]() / 2 - _0x1e7c95("#popup_container")["outerHeight"]() / 2 + _0x1e7c95["alerts"]["verticalOffset"];

      var _0x22ed28 = _0x1e7c95(window)["width"]() / 2 - _0x1e7c95("#popup_container")["outerWidth"]() / 2 + _0x1e7c95["alerts"]["horizontalOffset"];

      if (_0x29a8b9 < 0) {
        _0x29a8b9 = 0;
      }

      if (_0x22ed28 < 0) {
        _0x22ed28 = 0;
      }

      _0x1e7c95("#popup_container")["css"]({
        "top": _0x29a8b9 + "px",
        "left": _0x22ed28 + "px"
      });

      _0x1e7c95("#popup_overlay")["height"](_0x1e7c95(document)["height"]());
    },
    "_maintainPosition": function (_0x25ce47) {
      if (_0x1e7c95["alerts"]["repositionOnResize"]) {
        switch (_0x25ce47) {
          case true:
            _0x1e7c95(window)["bind"]("resize", _0x1e7c95["alerts"]["_reposition"]);

            break;

          case false:
            _0x1e7c95(window)["unbind"]("resize", _0x1e7c95["alerts"]["_reposition"]);

            break;
        }
      }
    }
  };

  jAlert = function (_0x4e0504, _0xc67bea, _0x1cf495) {
    _0x1e7c95["alerts"]["alert"](_0x4e0504, _0xc67bea, _0x1cf495);
  };

  jConfirm = function (_0x2291e3, _0x44337e, _0x3f57ad) {
    _0x1e7c95["alerts"]["confirm"](_0x2291e3, _0x44337e, _0x3f57ad);
  };

  jPrompt = function (_0x3ea78d, _0x1d7370, _0x370b14, _0x1371b8) {
    _0x1e7c95["alerts"]["prompt"](_0x3ea78d, _0x1d7370, _0x370b14, _0x1371b8);
  };
})(jQuery);

function checkPass(_0x4a8f16) {
  var _0x392377 = 0;

  if (_0x4a8f16["match"](/([a-z])+/)) {
    _0x392377++;
  }

  if (_0x4a8f16["match"](/([0-9])+/)) {
    _0x392377++;
  }

  if (_0x4a8f16["match"](/([A-Z])+/)) {
    _0x392377++;
  }

  if (_0x4a8f16["match"](/[^a-zA-Z0-9]+/)) {
    _0x392377++;
  }

  return _0x392377;
}

function afcdm(_0x39ea20) {
  swjgmcft = 1;

  if (_0x39ea20["length"] == 10 || _0x39ea20["length"] == 12) {
    var _0x23de2a = /^[0-9]*$/;

    var _0x50cd12 = _0x23de2a["test"](_0x39ea20);

    if (_0x50cd12 == false) {
      $("#fpdmjy")["addClass"]("tip_common_wrong");
      $("#fpdmjy")["addClass"]("font_red");
      $("#fpdmjy")["html"]("\u53D1\u7968\u4EE3\u7801\u6709\u8BEF!");
      return false;
    } else {
      var _0x41efde = $("#fpdmjy")["attr"]("class");

      $("#fpdmjy")["removeClass"](_0x41efde);

      var _0x545d38 = getSwjg(_0x39ea20, 0);

      if (_0x545d38["length"] == 0) {
        $("#fpdmjy")["addClass"]("tip_common_wrong");
        $("#fpdmjy")["addClass"]("font_red");
        $("#fpdmjy")["html"]("\u53D1\u7968\u4EE3\u7801\u6709\u8BEF!");
        return false;
      } else {
        if (!adm(_0x39ea20) || _0x545d38["length"] == 0) {
          $("#fpdmjy")["addClass"]("tip_common_wrong");
          $("#fpdmjy")["addClass"]("font_red");
          $("#fpdmjy")["html"]("\u53D1\u7968\u4EE3\u7801\u6709\u8BEF!");
          return false;
        } else {
          $("#fpdmjy")["addClass"]("tip_common_right");
          $("#fpdmjy")["html"]("&nbsp;");

          var _0x2668bf = $("#kjje")["val"]()["trim"]();

          var _0x407962 = $("#kprq")["val"]()["trim"]();

          fplx = alxd(_0x39ea20);
          swjgmc = _0x545d38[0];
          ip = _0x545d38[1];

          if (fplx == "01" || fplx == "02" || fplx == "03" || fplx == "15" || fplx == "08") {
            if (fplx == "02") {
              $("#context")["text"]("\u5408\u8BA1\u91D1\u989D\uFF1A");
              $("#kjjejy")["text"]("\u8BF7\u8F93\u5165\u5408\u8BA1\u91D1\u989D");
            } else {
              if (fplx == "03") {
                $("#context")["text"]("\u4E0D\u542B\u7A0E\u4EF7\uFF1A");
                $("#kjjejy")["text"]("\u8BF7\u8F93\u5165\u4E0D\u542B\u7A0E\u4EF7");
              } else {
                if (fplx == "15") {
                  $("#context")["text"]("\u8F66\u4EF7\u5408\u8BA1\uFF1A");
                  $("#kjjejy")["text"]("\u8BF7\u8F93\u5165\u8F66\u4EF7\u5408\u8BA1");
                } else {
                  $("#context")["text"]("\u5F00\u5177\u91D1\u989D(\u4E0D\u542B\u7A0E)\uFF1A");
                  $("#kjjejy")["text"]("\u8BF7\u8F93\u5165\u5F00\u5177\u91D1\u989D");
                }
              }
            }

            if (xsje != 1) {
              $("#kjjejy")["removeClass"]();
              $("#kjjejy")["addClass"]("tip_common");

              if (fplx == "02") {
                $("#context")["text"]("\u5408\u8BA1\u91D1\u989D\uFF1A");
                $("#kjjejy")["text"]("\u8BF7\u8F93\u5165\u5408\u8BA1\u91D1\u989D");
              } else {
                if (fplx == "03") {
                  $("#context")["text"]("\u4E0D\u542B\u7A0E\u4EF7\uFF1A");
                  $("#kjjejy")["text"]("\u8BF7\u8F93\u5165\u4E0D\u542B\u7A0E\u4EF7");
                } else {
                  if (fplx == "15") {
                    $("#context")["text"]("\u8F66\u4EF7\u5408\u8BA1\uFF1A");
                    $("#kjjejy")["text"]("\u8BF7\u8F93\u5165\u8F66\u4EF7\u5408\u8BA1");
                  } else {
                    $("#context")["text"]("\u5F00\u5177\u91D1\u989D(\u4E0D\u542B\u7A0E)\uFF1A");
                    $("#kjjejy")["text"]("\u8BF7\u8F93\u5165\u5F00\u5177\u91D1\u989D");
                  }
                }
              }
            } else {
              if (_0x2668bf != "") {
                if (!aje(_0x39ea20, _0x2668bf)) {
                  $("#kjjejy")["addClass"]("tip_common_wrong");
                  $("#kjjejy")["addClass"]("font_red");

                  if (fplx == "02") {
                    $("#kjjejy")["html"]("\u5408\u8BA1\u91D1\u989D\u6709\u8BEF!");
                  } else {
                    if (fplx == "03") {
                      $("#kjjejy")["html"]("\u4E0D\u542B\u7A0E\u4EF7\u6709\u8BEF!");
                    } else {
                      if (fplx == "15") {
                        $("#kjjejy")["html"]("\u8F66\u4EF7\u5408\u8BA1\u6709\u8BEF!");
                      } else {
                        $("#kjjejy")["html"]("\u5F00\u7968\u91D1\u989D\u6709\u8BEF!");
                      }
                    }
                  }
                } else {
                  $("#kjjejy")["addClass"]("tip_common_right");
                  $("#kjjejy")["html"]("&nbsp;");
                }
              }
            }

            xsje = 1;

            if (_0x407962 != "") {
              kprqChange(_0x407962);
            }
          } else {
            if (fplx == "04" || fplx == "10" || fplx == "11" || fplx == "14") {
              if (xsje != 0) {
                $("#kjjejy")["removeClass"]();
                $("#kjjejy")["addClass"]("tip_common");
                $("#context")["text"]("\u6821\u9A8C\u7801\uFF1A");
                $("#kjje")["attr"]("maxlength", "6");
                $("#kjjejy")["html"]("\u8BF7\u8F93\u5165\u6821\u9A8C\u7801<font color=\"red\" size=\"4\">\u540E\u516D\u4F4D</font>");
                xsje = 0;
              }

              if (_0x407962 != "") {
                kprqChange(_0x407962);
              }
            }
          }

          var _0x1959c0 = $("#fphm")["val"]()["trim"]();

          if (_0x1959c0 != "" && ahm(_0x1959c0) && yzmSj == "" && show_yzm == "") {
            $("#yzm_img")["show"]();
            getYzmXx();
          }
        }
      }
    }
  }
}
