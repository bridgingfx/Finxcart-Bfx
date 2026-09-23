"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = exports.EventFormatter = exports.Connector = exports.Channel = void 0;
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }
function _possibleConstructorReturn(t, e) { if (e && ("object" == _typeof(e) || "function" == typeof e)) return e; if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined"); return _assertThisInitialized(t); }
function _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); return e; }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }
function _inherits(t, e) { if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, "prototype", { writable: !1 }), e && _setPrototypeOf(t, e); }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var u = exports.Channel = /*#__PURE__*/function () {
  function u() {
    _classCallCheck(this, u);
    this.notificationCreatedEvent = ".Illuminate\\Notifications\\Events\\BroadcastNotificationCreated";
  }
  /**
   * Listen for a whisper event on the channel instance.
   */
  return _createClass(u, [{
    key: "listenForWhisper",
    value: function listenForWhisper(e, t) {
      return this.listen(".client-" + e, t);
    }
    /**
     * Listen for an event on the channel instance.
     */
  }, {
    key: "notification",
    value: function notification(e) {
      return this.listen(this.notificationCreatedEvent, e);
    }
    /**
     * Stop listening for notification events on the channel instance.
     */
  }, {
    key: "stopListeningForNotification",
    value: function stopListeningForNotification(e) {
      return this.stopListening(this.notificationCreatedEvent, e);
    }
    /**
     * Stop listening for a whisper event on the channel instance.
     */
  }, {
    key: "stopListeningForWhisper",
    value: function stopListeningForWhisper(e, t) {
      return this.stopListening(".client-" + e, t);
    }
  }]);
}();
var d = exports.EventFormatter = /*#__PURE__*/function () {
  /**
   * Create a new class instance.
   */
  function d(e) {
    _classCallCheck(this, d);
    this.namespace = e;
  }
  /**
   * Format the given event name.
   */
  return _createClass(d, [{
    key: "format",
    value: function format(e) {
      return [".", "\\"].includes(e.charAt(0)) ? e.substring(1) : (this.namespace && (e = this.namespace + "." + e), e.replace(/\./g, "\\"));
    }
    /**
     * Set the event namespace.
     */
  }, {
    key: "setNamespace",
    value: function setNamespace(e) {
      this.namespace = e;
    }
  }]);
}();
function w(n) {
  try {
    new n();
  } catch (e) {
    if (e instanceof Error && e.message.includes("is not a constructor")) return !1;
  }
  return !0;
}
var l = /*#__PURE__*/function (_u) {
  /**
   * Create a new class instance.
   */
  function l(e, t, s) {
    var _this;
    _classCallCheck(this, l);
    _this = _callSuper(this, l), _this.name = t, _this.pusher = e, _this.options = s, _this.eventFormatter = new d(_this.options.namespace), _this.subscribe();
    return _this;
  }
  /**
   * Subscribe to a Pusher channel.
   */
  _inherits(l, _u);
  return _createClass(l, [{
    key: "subscribe",
    value: function subscribe() {
      this.subscription = this.pusher.subscribe(this.name);
    }
    /**
     * Unsubscribe from a Pusher channel.
     */
  }, {
    key: "unsubscribe",
    value: function unsubscribe() {
      this.pusher.unsubscribe(this.name);
    }
    /**
     * Listen for an event on the channel instance.
     */
  }, {
    key: "listen",
    value: function listen(e, t) {
      return this.on(this.eventFormatter.format(e), t), this;
    }
    /**
     * Listen for all events on the channel instance.
     */
  }, {
    key: "listenToAll",
    value: function listenToAll(e) {
      var _this2 = this;
      return this.subscription.bind_global(function (t, s) {
        var _this2$options$namesp;
        if (t.startsWith("pusher:")) return;
        var r = String((_this2$options$namesp = _this2.options.namespace) !== null && _this2$options$namesp !== void 0 ? _this2$options$namesp : "").replace(/\./g, "\\"),
          a = t.startsWith(r) ? t.substring(r.length + 1) : "." + t;
        e(a, s);
      }), this;
    }
    /**
     * Stop listening for an event on the channel instance.
     */
  }, {
    key: "stopListening",
    value: function stopListening(e, t) {
      return t ? this.subscription.unbind(this.eventFormatter.format(e), t) : this.subscription.unbind(this.eventFormatter.format(e)), this;
    }
    /**
     * Stop listening for all events on the channel instance.
     */
  }, {
    key: "stopListeningToAll",
    value: function stopListeningToAll(e) {
      return e ? this.subscription.unbind_global(e) : this.subscription.unbind_global(), this;
    }
    /**
     * Register a callback to be called anytime a subscription succeeds.
     */
  }, {
    key: "subscribed",
    value: function subscribed(e) {
      return this.on("pusher:subscription_succeeded", function () {
        e();
      }), this;
    }
    /**
     * Register a callback to be called anytime a subscription error occurs.
     */
  }, {
    key: "error",
    value: function error(e) {
      return this.on("pusher:subscription_error", function (t) {
        e(t);
      }), this;
    }
    /**
     * Bind a channel to an event.
     */
  }, {
    key: "on",
    value: function on(e, t) {
      return this.subscription.bind(e, t), this;
    }
  }]);
}(u);
var f = /*#__PURE__*/function (_l) {
  function f() {
    _classCallCheck(this, f);
    return _callSuper(this, f, arguments);
  }
  _inherits(f, _l);
  return _createClass(f, [{
    key: "whisper",
    value:
    /**
     * Send a whisper event to other clients in the channel.
     */
    function whisper(e, t) {
      return this.pusher.channels.channels[this.name].trigger("client-".concat(e), t), this;
    }
  }]);
}(l);
var g = /*#__PURE__*/function (_l2) {
  function g() {
    _classCallCheck(this, g);
    return _callSuper(this, g, arguments);
  }
  _inherits(g, _l2);
  return _createClass(g, [{
    key: "whisper",
    value:
    /**
     * Send a whisper event to other clients in the channel.
     */
    function whisper(e, t) {
      return this.pusher.channels.channels[this.name].trigger("client-".concat(e), t), this;
    }
  }]);
}(l);
var y = /*#__PURE__*/function (_f) {
  function y() {
    _classCallCheck(this, y);
    return _callSuper(this, y, arguments);
  }
  _inherits(y, _f);
  return _createClass(y, [{
    key: "here",
    value:
    /**
     * Register a callback to be called anytime the member list changes.
     */
    function here(e) {
      return this.on("pusher:subscription_succeeded", function (t) {
        e(Object.keys(t.members).map(function (s) {
          return t.members[s];
        }));
      }), this;
    }
    /**
     * Listen for someone joining the channel.
     */
  }, {
    key: "joining",
    value: function joining(e) {
      return this.on("pusher:member_added", function (t) {
        e(t.info);
      }), this;
    }
    /**
     * Send a whisper event to other clients in the channel.
     */
  }, {
    key: "whisper",
    value: function whisper(e, t) {
      return this.pusher.channels.channels[this.name].trigger("client-".concat(e), t), this;
    }
    /**
     * Listen for someone leaving the channel.
     */
  }, {
    key: "leaving",
    value: function leaving(e) {
      return this.on("pusher:member_removed", function (t) {
        e(t.info);
      }), this;
    }
  }]);
}(f);
var b = /*#__PURE__*/function (_u2) {
  /**
   * Create a new class instance.
   */
  function b(e, t, s) {
    var _this3;
    _classCallCheck(this, b);
    _this3 = _callSuper(this, b), _this3.events = {}, _this3.listeners = {}, _this3.name = t, _this3.socket = e, _this3.options = s, _this3.eventFormatter = new d(_this3.options.namespace), _this3.subscribe();
    return _this3;
  }
  /**
   * Subscribe to a Socket.io channel.
   */
  _inherits(b, _u2);
  return _createClass(b, [{
    key: "subscribe",
    value: function subscribe() {
      this.socket.emit("subscribe", {
        channel: this.name,
        auth: this.options.auth || {}
      });
    }
    /**
     * Unsubscribe from channel and ubind event callbacks.
     */
  }, {
    key: "unsubscribe",
    value: function unsubscribe() {
      this.unbind(), this.socket.emit("unsubscribe", {
        channel: this.name,
        auth: this.options.auth || {}
      });
    }
    /**
     * Listen for an event on the channel instance.
     */
  }, {
    key: "listen",
    value: function listen(e, t) {
      return this.on(this.eventFormatter.format(e), t), this;
    }
    /**
     * Stop listening for an event on the channel instance.
     */
  }, {
    key: "stopListening",
    value: function stopListening(e, t) {
      return this.unbindEvent(this.eventFormatter.format(e), t), this;
    }
    /**
     * Register a callback to be called anytime a subscription succeeds.
     */
  }, {
    key: "subscribed",
    value: function subscribed(e) {
      return this.on("connect", function (t) {
        e(t);
      }), this;
    }
    /**
     * Register a callback to be called anytime an error occurs.
     */
  }, {
    key: "error",
    value: function error(e) {
      return this;
    }
    /**
     * Bind the channel's socket to an event and store the callback.
     */
  }, {
    key: "on",
    value: function on(e, t) {
      var _this4 = this;
      return this.listeners[e] = this.listeners[e] || [], this.events[e] || (this.events[e] = function (s, r) {
        _this4.name === s && _this4.listeners[e] && _this4.listeners[e].forEach(function (a) {
          return a(r);
        });
      }, this.socket.on(e, this.events[e])), this.listeners[e].push(t), this;
    }
    /**
     * Unbind the channel's socket from all stored event callbacks.
     */
  }, {
    key: "unbind",
    value: function unbind() {
      var _this5 = this;
      Object.keys(this.events).forEach(function (e) {
        _this5.unbindEvent(e);
      });
    }
    /**
     * Unbind the listeners for the given event.
     */
  }, {
    key: "unbindEvent",
    value: function unbindEvent(e, t) {
      this.listeners[e] = this.listeners[e] || [], t && (this.listeners[e] = this.listeners[e].filter(function (s) {
        return s !== t;
      })), (!t || this.listeners[e].length === 0) && (this.events[e] && (this.socket.removeListener(e, this.events[e]), delete this.events[e]), delete this.listeners[e]);
    }
  }]);
}(u);
var v = /*#__PURE__*/function (_b) {
  function v() {
    _classCallCheck(this, v);
    return _callSuper(this, v, arguments);
  }
  _inherits(v, _b);
  return _createClass(v, [{
    key: "whisper",
    value:
    /**
     * Send a whisper event to other clients in the channel.
     */
    function whisper(e, t) {
      return this.socket.emit("client event", {
        channel: this.name,
        event: "client-".concat(e),
        data: t
      }), this;
    }
  }]);
}(b);
var m = /*#__PURE__*/function (_v) {
  function m() {
    _classCallCheck(this, m);
    return _callSuper(this, m, arguments);
  }
  _inherits(m, _v);
  return _createClass(m, [{
    key: "here",
    value:
    /**
     * Register a callback to be called anytime the member list changes.
     */
    function here(e) {
      return this.on("presence:subscribed", function (t) {
        e(t.map(function (s) {
          return s.user_info;
        }));
      }), this;
    }
    /**
     * Listen for someone joining the channel.
     */
  }, {
    key: "joining",
    value: function joining(e) {
      return this.on("presence:joining", function (t) {
        return e(t.user_info);
      }), this;
    }
    /**
     * Send a whisper event to other clients in the channel.
     */
  }, {
    key: "whisper",
    value: function whisper(e, t) {
      return this.socket.emit("client event", {
        channel: this.name,
        event: "client-".concat(e),
        data: t
      }), this;
    }
    /**
     * Listen for someone leaving the channel.
     */
  }, {
    key: "leaving",
    value: function leaving(e) {
      return this.on("presence:leaving", function (t) {
        return e(t.user_info);
      }), this;
    }
  }]);
}(v);
var h = /*#__PURE__*/function (_u3) {
  function h() {
    _classCallCheck(this, h);
    return _callSuper(this, h, arguments);
  }
  _inherits(h, _u3);
  return _createClass(h, [{
    key: "subscribe",
    value:
    /**
     * Subscribe to a channel.
     */
    function subscribe() {}
    /**
     * Unsubscribe from a channel.
     */
  }, {
    key: "unsubscribe",
    value: function unsubscribe() {}
    /**
     * Listen for an event on the channel instance.
     */
  }, {
    key: "listen",
    value: function listen(e, t) {
      return this;
    }
    /**
     * Listen for all events on the channel instance.
     */
  }, {
    key: "listenToAll",
    value: function listenToAll(e) {
      return this;
    }
    /**
     * Stop listening for an event on the channel instance.
     */
  }, {
    key: "stopListening",
    value: function stopListening(e, t) {
      return this;
    }
    /**
     * Register a callback to be called anytime a subscription succeeds.
     */
  }, {
    key: "subscribed",
    value: function subscribed(e) {
      return this;
    }
    /**
     * Register a callback to be called anytime an error occurs.
     */
  }, {
    key: "error",
    value: function error(e) {
      return this;
    }
    /**
     * Bind a channel to an event.
     */
  }, {
    key: "on",
    value: function on(e, t) {
      return this;
    }
  }]);
}(u);
var k = /*#__PURE__*/function (_h) {
  function k() {
    _classCallCheck(this, k);
    return _callSuper(this, k, arguments);
  }
  _inherits(k, _h);
  return _createClass(k, [{
    key: "whisper",
    value:
    /**
     * Send a whisper event to other clients in the channel.
     */
    function whisper(e, t) {
      return this;
    }
  }]);
}(h);
var C = /*#__PURE__*/function (_h2) {
  function C() {
    _classCallCheck(this, C);
    return _callSuper(this, C, arguments);
  }
  _inherits(C, _h2);
  return _createClass(C, [{
    key: "whisper",
    value:
    /**
     * Send a whisper event to other clients in the channel.
     */
    function whisper(e, t) {
      return this;
    }
  }]);
}(h);
var _ = /*#__PURE__*/function (_k) {
  function _() {
    _classCallCheck(this, _);
    return _callSuper(this, _, arguments);
  }
  _inherits(_, _k);
  return _createClass(_, [{
    key: "here",
    value:
    /**
     * Register a callback to be called anytime the member list changes.
     */
    function here(e) {
      return this;
    }
    /**
     * Listen for someone joining the channel.
     */
  }, {
    key: "joining",
    value: function joining(e) {
      return this;
    }
    /**
     * Send a whisper event to other clients in the channel.
     */
  }, {
    key: "whisper",
    value: function whisper(e, t) {
      return this;
    }
    /**
     * Listen for someone leaving the channel.
     */
  }, {
    key: "leaving",
    value: function leaving(e) {
      return this;
    }
  }]);
}(k);
var c = /*#__PURE__*/function () {
  /**
   * Create a new class instance.
   */
  function c(e) {
    _classCallCheck(this, c);
    this.setOptions(e), this.connect();
  }
  /**
   * Merge the custom options with the defaults.
   */
  return _createClass(c, [{
    key: "setOptions",
    value: function setOptions(e) {
      this.options = _objectSpread(_objectSpread(_objectSpread({}, c._defaultOptions), e), {}, {
        broadcaster: e.broadcaster
      });
      var t = this.csrfToken();
      t && (this.options.auth.headers["X-CSRF-TOKEN"] = t, this.options.userAuthentication.headers["X-CSRF-TOKEN"] = t), t = this.options.bearerToken, t && (this.options.auth.headers.Authorization = "Bearer " + t, this.options.userAuthentication.headers.Authorization = "Bearer " + t);
    }
    /**
     * Extract the CSRF token from the page.
     */
  }, {
    key: "csrfToken",
    value: function csrfToken() {
      var _ref;
      var e, t;
      return (typeof window === "undefined" ? "undefined" : _typeof(window)) < "u" && (e = window.Laravel) != null && e.csrfToken ? window.Laravel.csrfToken : this.options.csrfToken ? this.options.csrfToken : (typeof document === "undefined" ? "undefined" : _typeof(document)) < "u" && typeof document.querySelector == "function" ? (_ref = (t = document.querySelector('meta[name="csrf-token"]')) == null ? void 0 : t.getAttribute("content")) !== null && _ref !== void 0 ? _ref : null : null;
    }
  }]);
}();
c._defaultOptions = {
  auth: {
    headers: {}
  },
  authEndpoint: "/broadcasting/auth",
  userAuthentication: {
    endpoint: "/broadcasting/user-auth",
    headers: {}
  },
  csrfToken: null,
  bearerToken: null,
  host: null,
  key: null,
  namespace: "App.Events"
};
var i = exports.Connector = c;
var o = /*#__PURE__*/function (_i) {
  function o() {
    var _this6;
    _classCallCheck(this, o);
    _this6 = _callSuper(this, o, arguments), _this6.channels = {};
    return _this6;
  }
  /**
   * Create a fresh Pusher connection.
   */
  _inherits(o, _i);
  return _createClass(o, [{
    key: "connect",
    value: function connect() {
      if (_typeof(this.options.client) < "u") this.pusher = this.options.client;else if (this.options.Pusher) this.pusher = new this.options.Pusher(this.options.key, this.options);else if ((typeof window === "undefined" ? "undefined" : _typeof(window)) < "u" && _typeof(window.Pusher) < "u") this.pusher = new window.Pusher(this.options.key, this.options);else throw new Error("Pusher client not found. Should be globally available or passed via options.client");
    }
    /**
     * Sign in the user via Pusher user authentication (https://pusher.com/docs/channels/using_channels/user-authentication/).
     */
  }, {
    key: "signin",
    value: function signin() {
      this.pusher.signin();
    }
    /**
     * Listen for an event on a channel instance.
     */
  }, {
    key: "listen",
    value: function listen(e, t, s) {
      return this.channel(e).listen(t, s);
    }
    /**
     * Get a channel instance by name.
     */
  }, {
    key: "channel",
    value: function channel(e) {
      return this.channels[e] || (this.channels[e] = new l(this.pusher, e, this.options)), this.channels[e];
    }
    /**
     * Get a private channel instance by name.
     */
  }, {
    key: "privateChannel",
    value: function privateChannel(e) {
      return this.channels["private-" + e] || (this.channels["private-" + e] = new f(this.pusher, "private-" + e, this.options)), this.channels["private-" + e];
    }
    /**
     * Get a private encrypted channel instance by name.
     */
  }, {
    key: "encryptedPrivateChannel",
    value: function encryptedPrivateChannel(e) {
      return this.channels["private-encrypted-" + e] || (this.channels["private-encrypted-" + e] = new g(this.pusher, "private-encrypted-" + e, this.options)), this.channels["private-encrypted-" + e];
    }
    /**
     * Get a presence channel instance by name.
     */
  }, {
    key: "presenceChannel",
    value: function presenceChannel(e) {
      return this.channels["presence-" + e] || (this.channels["presence-" + e] = new y(this.pusher, "presence-" + e, this.options)), this.channels["presence-" + e];
    }
    /**
     * Leave the given channel, as well as its private and presence variants.
     */
  }, {
    key: "leave",
    value: function leave(e) {
      var _this7 = this;
      [e, "private-" + e, "private-encrypted-" + e, "presence-" + e].forEach(function (s) {
        _this7.leaveChannel(s);
      });
    }
    /**
     * Leave the given channel.
     */
  }, {
    key: "leaveChannel",
    value: function leaveChannel(e) {
      this.channels[e] && (this.channels[e].unsubscribe(), delete this.channels[e]);
    }
    /**
     * Get the socket ID for the connection.
     */
  }, {
    key: "socketId",
    value: function socketId() {
      return this.pusher.connection.socket_id;
    }
    /**
     * Disconnect Pusher connection.
     */
  }, {
    key: "disconnect",
    value: function disconnect() {
      this.pusher.disconnect();
    }
  }]);
}(i);
var I = /*#__PURE__*/function (_i2) {
  function I() {
    var _this8;
    _classCallCheck(this, I);
    _this8 = _callSuper(this, I, arguments), _this8.channels = {};
    return _this8;
  }
  /**
   * Create a fresh Socket.io connection.
   */
  _inherits(I, _i2);
  return _createClass(I, [{
    key: "connect",
    value: function connect() {
      var _this$options$host,
        _this9 = this;
      var e = this.getSocketIO();
      this.socket = e((_this$options$host = this.options.host) !== null && _this$options$host !== void 0 ? _this$options$host : void 0, this.options), this.socket.io.on("reconnect", function () {
        Object.values(_this9.channels).forEach(function (t) {
          t.subscribe();
        });
      });
    }
    /**
     * Get socket.io module from global scope or options.
     */
  }, {
    key: "getSocketIO",
    value: function getSocketIO() {
      if (_typeof(this.options.client) < "u") return this.options.client;
      if ((typeof window === "undefined" ? "undefined" : _typeof(window)) < "u" && _typeof(window.io) < "u") return window.io;
      throw new Error("Socket.io client not found. Should be globally available or passed via options.client");
    }
    /**
     * Listen for an event on a channel instance.
     */
  }, {
    key: "listen",
    value: function listen(e, t, s) {
      return this.channel(e).listen(t, s);
    }
    /**
     * Get a channel instance by name.
     */
  }, {
    key: "channel",
    value: function channel(e) {
      return this.channels[e] || (this.channels[e] = new b(this.socket, e, this.options)), this.channels[e];
    }
    /**
     * Get a private channel instance by name.
     */
  }, {
    key: "privateChannel",
    value: function privateChannel(e) {
      return this.channels["private-" + e] || (this.channels["private-" + e] = new v(this.socket, "private-" + e, this.options)), this.channels["private-" + e];
    }
    /**
     * Get a presence channel instance by name.
     */
  }, {
    key: "presenceChannel",
    value: function presenceChannel(e) {
      return this.channels["presence-" + e] || (this.channels["presence-" + e] = new m(this.socket, "presence-" + e, this.options)), this.channels["presence-" + e];
    }
    /**
     * Leave the given channel, as well as its private and presence variants.
     */
  }, {
    key: "leave",
    value: function leave(e) {
      var _this0 = this;
      [e, "private-" + e, "presence-" + e].forEach(function (s) {
        _this0.leaveChannel(s);
      });
    }
    /**
     * Leave the given channel.
     */
  }, {
    key: "leaveChannel",
    value: function leaveChannel(e) {
      this.channels[e] && (this.channels[e].unsubscribe(), delete this.channels[e]);
    }
    /**
     * Get the socket ID for the connection.
     */
  }, {
    key: "socketId",
    value: function socketId() {
      return this.socket.id;
    }
    /**
     * Disconnect Socketio connection.
     */
  }, {
    key: "disconnect",
    value: function disconnect() {
      this.socket.disconnect();
    }
  }]);
}(i);
var p = /*#__PURE__*/function (_i3) {
  function p() {
    var _this1;
    _classCallCheck(this, p);
    _this1 = _callSuper(this, p, arguments), _this1.channels = {};
    return _this1;
  }
  /**
   * Create a fresh connection.
   */
  _inherits(p, _i3);
  return _createClass(p, [{
    key: "connect",
    value: function connect() {}
    /**
     * Listen for an event on a channel instance.
     */
  }, {
    key: "listen",
    value: function listen(e, t, s) {
      return new h();
    }
    /**
     * Get a channel instance by name.
     */
  }, {
    key: "channel",
    value: function channel(e) {
      return new h();
    }
    /**
     * Get a private channel instance by name.
     */
  }, {
    key: "privateChannel",
    value: function privateChannel(e) {
      return new k();
    }
    /**
     * Get a private encrypted channel instance by name.
     */
  }, {
    key: "encryptedPrivateChannel",
    value: function encryptedPrivateChannel(e) {
      return new C();
    }
    /**
     * Get a presence channel instance by name.
     */
  }, {
    key: "presenceChannel",
    value: function presenceChannel(e) {
      return new _();
    }
    /**
     * Leave the given channel, as well as its private and presence variants.
     */
  }, {
    key: "leave",
    value: function leave(e) {}
    /**
     * Leave the given channel.
     */
  }, {
    key: "leaveChannel",
    value: function leaveChannel(e) {}
    /**
     * Get the socket ID for the connection.
     */
  }, {
    key: "socketId",
    value: function socketId() {
      return "fake-socket-id";
    }
    /**
     * Disconnect the connection.
     */
  }, {
    key: "disconnect",
    value: function disconnect() {}
  }]);
}(i);
var E = exports.default = /*#__PURE__*/function () {
  /**
   * Create a new class instance.
   */
  function E(e) {
    _classCallCheck(this, E);
    this.options = e, this.connect(), this.options.withoutInterceptors || this.registerInterceptors();
  }
  /**
   * Get a channel instance by name.
   */
  return _createClass(E, [{
    key: "channel",
    value: function channel(e) {
      return this.connector.channel(e);
    }
    /**
     * Create a new connection.
     */
  }, {
    key: "connect",
    value: function connect() {
      if (this.options.broadcaster === "reverb") this.connector = new o(_objectSpread(_objectSpread({}, this.options), {}, {
        cluster: ""
      }));else if (this.options.broadcaster === "pusher") this.connector = new o(this.options);else if (this.options.broadcaster === "ably") this.connector = new o(_objectSpread(_objectSpread({}, this.options), {}, {
        cluster: "",
        broadcaster: "pusher"
      }));else if (this.options.broadcaster === "socket.io") this.connector = new I(this.options);else if (this.options.broadcaster === "null") this.connector = new p(this.options);else if (typeof this.options.broadcaster == "function" && w(this.options.broadcaster)) this.connector = new this.options.broadcaster(this.options);else throw new Error("Broadcaster ".concat(_typeof(this.options.broadcaster), " ").concat(String(this.options.broadcaster), " is not supported."));
    }
    /**
     * Disconnect from the Echo server.
     */
  }, {
    key: "disconnect",
    value: function disconnect() {
      this.connector.disconnect();
    }
    /**
     * Get a presence channel instance by name.
     */
  }, {
    key: "join",
    value: function join(e) {
      return this.connector.presenceChannel(e);
    }
    /**
     * Leave the given channel, as well as its private and presence variants.
     */
  }, {
    key: "leave",
    value: function leave(e) {
      this.connector.leave(e);
    }
    /**
     * Leave the given channel.
     */
  }, {
    key: "leaveChannel",
    value: function leaveChannel(e) {
      this.connector.leaveChannel(e);
    }
    /**
     * Leave all channels.
     */
  }, {
    key: "leaveAllChannels",
    value: function leaveAllChannels() {
      for (var e in this.connector.channels) this.leaveChannel(e);
    }
    /**
     * Listen for an event on a channel instance.
     */
  }, {
    key: "listen",
    value: function listen(e, t, s) {
      return this.connector.listen(e, t, s);
    }
    /**
     * Get a private channel instance by name.
     */
  }, {
    key: "private",
    value: function _private(e) {
      return this.connector.privateChannel(e);
    }
    /**
     * Get a private encrypted channel instance by name.
     */
  }, {
    key: "encryptedPrivate",
    value: function encryptedPrivate(e) {
      if (this.connectorSupportsEncryptedPrivateChannels(this.connector)) return this.connector.encryptedPrivateChannel(e);
      throw new Error("Broadcaster ".concat(_typeof(this.options.broadcaster), " ").concat(String(this.options.broadcaster), " does not support encrypted private channels."));
    }
  }, {
    key: "connectorSupportsEncryptedPrivateChannels",
    value: function connectorSupportsEncryptedPrivateChannels(e) {
      return e instanceof o || e instanceof p;
    }
    /**
     * Get the Socket ID for the connection.
     */
  }, {
    key: "socketId",
    value: function socketId() {
      return this.connector.socketId();
    }
    /**
     * Register 3rd party request interceptiors. These are used to automatically
     * send a connections socket id to a Laravel app with a X-Socket-Id header.
     */
  }, {
    key: "registerInterceptors",
    value: function registerInterceptors() {
      (typeof Vue === "undefined" ? "undefined" : _typeof(Vue)) < "u" && Vue != null && Vue.http && this.registerVueRequestInterceptor(), typeof axios == "function" && this.registerAxiosRequestInterceptor(), typeof jQuery == "function" && this.registerjQueryAjaxSetup(), (typeof Turbo === "undefined" ? "undefined" : _typeof(Turbo)) == "object" && this.registerTurboRequestInterceptor();
    }
    /**
     * Register a Vue HTTP interceptor to add the X-Socket-ID header.
     */
  }, {
    key: "registerVueRequestInterceptor",
    value: function registerVueRequestInterceptor() {
      var _this10 = this;
      Vue.http.interceptors.push(function (e, t) {
        _this10.socketId() && e.headers.set("X-Socket-ID", _this10.socketId()), t();
      });
    }
    /**
     * Register an Axios HTTP interceptor to add the X-Socket-ID header.
     */
  }, {
    key: "registerAxiosRequestInterceptor",
    value: function registerAxiosRequestInterceptor() {
      var _this11 = this;
      axios.interceptors.request.use(function (e) {
        return _this11.socketId() && (e.headers["X-Socket-Id"] = _this11.socketId()), e;
      });
    }
    /**
     * Register jQuery AjaxPrefilter to add the X-Socket-ID header.
     */
  }, {
    key: "registerjQueryAjaxSetup",
    value: function registerjQueryAjaxSetup() {
      var _this12 = this;
      _typeof(jQuery.ajax) < "u" && jQuery.ajaxPrefilter(function (e, t, s) {
        _this12.socketId() && s.setRequestHeader("X-Socket-Id", _this12.socketId());
      });
    }
    /**
     * Register the Turbo Request interceptor to add the X-Socket-ID header.
     */
  }, {
    key: "registerTurboRequestInterceptor",
    value: function registerTurboRequestInterceptor() {
      var _this13 = this;
      document.addEventListener("turbo:before-fetch-request", function (e) {
        e.detail.fetchOptions.headers["X-Socket-Id"] = _this13.socketId();
      });
    }
  }]);
}();