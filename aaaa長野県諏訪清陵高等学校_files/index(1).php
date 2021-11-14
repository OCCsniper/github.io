
var debug = new debug();
function debug() {
this.html = "";
this.hWin = null;
this.bDebug = true;
this.now_bgcolor = "";
this.bgcolor1    = "#FAF5D4";
this.bgcolor2    = "#EBCC67";
this.level = 0;
this.maxLevel = 10;
this.setDebug = function(flag) {
this.bDebug = flag;
}
this.clear = function() {
this.html = "";
this.flush();
}
this.flush = function() {
if (false == this.bDebug) return;
if (null == this.hWin || this.hWin.closed) {
this.hWin = window.open("", "debug",
"height=200,width=400,menubar=yes,scrollbars=yes,resizable=yes");
}
this.hWin.document.open("text/html", "replace");
this.hWin.document.write(this.html);
this.hWin.document.close();
this.hWin.focus();
}
this.print = function(html) {
function parseHtml(str){
str = str.replace(/&/g, "&amp;");
str = str.replace(/</g, "&lt;");
str = str.replace(/>/g, "&gt;");
str = str.replace(/\"/g, "&quot;");
str = str.replace(/\n/g, "<br>\n");
return str;
}
this.now_bgcolor = (this.now_bgcolor == this.bgcolor1) ? this.bgcolor2 : this.bgcolor1;
this.html += ("<div style='background-color:"+this.now_bgcolor+"'>" + parseHtml(html) + "</div>\n");
}
this.inspect = function(obj) {
if (typeof obj == "number") {
return ""+obj;
} else if (typeof obj == "string") {
return "\""+obj+"\"";
} else if (typeof obj == "function") {
return ""+obj;
} else if (typeof obj == "object" && obj != null) {
if(!obj.tagName) {
var str = this.to_s(obj, "");
} else {
return "<"+(typeof obj)+":"+obj.tagName+">";
}
return "{"+str+"}";
} else {
return "<"+(typeof obj)+":"+obj+">";
}
}
this.to_s = function(obj, indent){
var delimiter = ", \n";
var inner_indent = "　　";
this.level += 1;
if(this.maxLevel < this.level){
return ""+this.maxLevel+"階層以上は省略します";
}
var str = "";
for (key in obj) {
if (str != "") str += delimiter;
str += indent;
var value = obj[key];
if (!value) {
str += ""+key+"=>undefined";
continue;
}
if (typeof value == "number") {
str += ""+key+"=>"+value+"";
} else if (typeof value == "string") {
str += ""+key+'=>"'+value+'"';
} else if (typeof value == "function") {
str += ""+key+"()";
} else if (typeof value == "object") {
value = "\n" + this.to_s(value, indent+inner_indent);
str += ""+key+"=>"+value+"";
} else {
str += ""+key+"=><"+(typeof value)+":"+value+">";
}
}
if (str == ""){
str += ""+obj;
}
this.level -= 1;
return str;
}
this.p = function(elem) {
this.print(this.inspect(elem));
this.flush();
}
}


var Prototype = {
Version: '1.5.0',
BrowserFeatures: {
XPath: !!document.evaluate
},
ScriptFragment: '(?:<script.*?>)((\n|\r|.)*?)(?:<\/script>)',
emptyFunction: function() {},
K: function(x) { return x }
}
var Class = {
create: function() {
return function() {
this.initialize.apply(this, arguments);
}
}
}
var Abstract = new Object();
Object.extend = function(destination, source) {
for (var property in source) {
destination[property] = source[property];
}
return destination;
}
Object.extend(Object, {
inspect: function(object) {
try {
if (object === undefined) return 'undefined';
if (object === null) return 'null';
return object.inspect ? object.inspect() : object.toString();
} catch (e) {
if (e instanceof RangeError) return '...';
throw e;
}
},
keys: function(object) {
var keys = [];
for (var property in object)
keys.push(property);
return keys;
},
values: function(object) {
var values = [];
for (var property in object)
values.push(object[property]);
return values;
},
clone: function(object) {
return Object.extend({}, object);
}
});
Function.prototype.bind = function() {
var __method = this, args = $A(arguments), object = args.shift();
return function() {
return __method.apply(object, args.concat($A(arguments)));
}
}
Function.prototype.bindAsEventListener = function(object) {
var __method = this, args = $A(arguments), object = args.shift();
return function(event) {
return __method.apply(object, [( event || window.event)].concat(args).concat($A(arguments)));
}
}
Object.extend(Number.prototype, {
toColorPart: function() {
var digits = this.toString(16);
if (this < 16) return '0' + digits;
return digits;
},
succ: function() {
return this + 1;
},
times: function(iterator) {
$R(0, this, true).each(iterator);
return this;
}
});
var Try = {
these: function() {
var returnValue;
for (var i = 0, length = arguments.length; i < length; i++) {
var lambda = arguments[i];
try {
returnValue = lambda();
break;
} catch (e) {}
}
return returnValue;
}
}
var PeriodicalExecuter = Class.create();
PeriodicalExecuter.prototype = {
initialize: function(callback, frequency) {
this.callback = callback;
this.frequency = frequency;
this.currentlyExecuting = false;
this.registerCallback();
},
registerCallback: function() {
this.timer = setInterval(this.onTimerEvent.bind(this), this.frequency * 1000);
},
stop: function() {
if (!this.timer) return;
clearInterval(this.timer);
this.timer = null;
},
onTimerEvent: function() {
if (!this.currentlyExecuting) {
try {
this.currentlyExecuting = true;
this.callback(this);
} finally {
this.currentlyExecuting = false;
}
}
}
}
String.interpret = function(value){
return value == null ? '' : String(value);
}
Object.extend(String.prototype, {
gsub: function(pattern, replacement) {
var result = '', source = this, match;
replacement = arguments.callee.prepareReplacement(replacement);
while (source.length > 0) {
if (match = source.match(pattern)) {
result += source.slice(0, match.index);
result += String.interpret(replacement(match));
source  = source.slice(match.index + match[0].length);
} else {
result += source, source = '';
}
}
return result;
},
sub: function(pattern, replacement, count) {
replacement = this.gsub.prepareReplacement(replacement);
count = count === undefined ? 1 : count;
return this.gsub(pattern, function(match) {
if (--count < 0) return match[0];
return replacement(match);
});
},
scan: function(pattern, iterator) {
this.gsub(pattern, iterator);
return this;
},
truncate: function(length, truncation) {
length = length || 30;
truncation = truncation === undefined ? '...' : truncation;
return this.length > length ?
this.slice(0, length - truncation.length) + truncation : this;
},
strip: function() {
return this.replace(/^\s+/, '').replace(/\s+$/, '');
},
trim: function() {
return this.strip();
},
stripTags: function() {
return this.replace(/<\/?[^>]+>/gi, '');
},
stripScripts: function() {
return this.replace(new RegExp(Prototype.ScriptFragment, 'img'), '');
},
extractScripts: function() {
var matchAll = new RegExp(Prototype.ScriptFragment, 'img');
var matchOne = new RegExp(Prototype.ScriptFragment, 'im');
return (this.match(matchAll) || []).map(function(scriptTag) {
return (scriptTag.match(matchOne) || ['', ''])[1];
});
},
evalScripts: function() {
return this.extractScripts().map(function(script) { return eval(script) });
},
escapeHTML: function() {
return String(this).replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;").replace(/ /g, "&nbsp;");
},
unescapeHTML: function() {
return String(this).replace(/&quot;/g,'"').replace(/&lt;/g,'<').replace(/&gt;/g,'>').replace(/&quot;/g, '"').replace(/&apos;/g, "'").replace(/&#039;/g, "'").replace(/&nbsp;/g, " ").replace(/&amp;/g,'&');
},
toQueryParams: function(separator) {
var match = this.strip().match(/([^?#]*)(#.*)?$/);
if (!match) return {};
return match[1].split(separator || '&').inject({}, function(hash, pair) {
if ((pair = pair.split('='))[0]) {
var name = decodeURIComponent(pair[0]);
var value = pair[1] ? decodeURIComponent(pair[1]) : "";
if (hash[name] !== undefined) {
if (hash[name].constructor != Array)
hash[name] = [hash[name]];
if (value) hash[name].push(value);
}
else hash[name] = value;
}
return hash;
});
},
toArray: function() {
return this.split('');
},
succ: function() {
return this.slice(0, this.length - 1) +
String.fromCharCode(this.charCodeAt(this.length - 1) + 1);
},
camelize: function() {
var parts = this.split('-'), len = parts.length;
if (len == 1) return parts[0];
var camelized = this.charAt(0) == '-'
? parts[0].charAt(0).toUpperCase() + parts[0].substring(1)
: parts[0];
for (var i = 1; i < len; i++)
camelized += parts[i].charAt(0).toUpperCase() + parts[i].substring(1);
return camelized;
},
capitalize: function(){
return this.charAt(0).toUpperCase() + this.substring(1).toLowerCase();
},
underscore: function() {
return this.gsub(/::/, '/').gsub(/([A-Z]+)([A-Z][a-z])/,'#{1}_#{2}').gsub(/([a-z\d])([A-Z])/,'#{1}_#{2}').gsub(/-/,'_').toLowerCase();
},
dasherize: function() {
return this.gsub(/_/,'-');
},
inspect: function(useDoubleQuotes) {
var escapedString = this.replace(/\\/g, '\\\\');
if (useDoubleQuotes)
return '"' + escapedString.replace(/"/g, '\\"') + '"';
else
return "'" + escapedString.replace(/'/g, '\\\'') + "'";
}
});
String.prototype.gsub.prepareReplacement = function(replacement) {
if (typeof replacement == 'function') return replacement;
var template = new Template(replacement);
return function(match) { return template.evaluate(match) };
}
String.prototype.parseQuery = String.prototype.toQueryParams;
var Template = Class.create();
Template.Pattern = /(^|.|\r|\n)(#\{(.*?)\})/;
Template.prototype = {
initialize: function(template, pattern) {
this.template = template.toString();
this.pattern  = pattern || Template.Pattern;
},
evaluate: function(object) {
return this.template.gsub(this.pattern, function(match) {
var before = match[1];
if (before == '\\') return match[2];
return before + String.interpret(object[match[3]]);
});
}
}
var $break    = new Object();
var $continue = new Object();
var Enumerable = {
each: function(iterator) {
var index = 0;
try {
this._each(function(value) {
try {
iterator(value, index++);
} catch (e) {
if (e != $continue) throw e;
}
});
} catch (e) {
if(!(browser.isIE && browser.version >= 9)) {
if (e != $break) throw e;
}
}
return this;
},
eachSlice: function(number, iterator) {
var index = -number, slices = [], array = this.toArray();
while ((index += number) < array.length)
slices.push(array.slice(index, index+number));
return slices.map(iterator);
},
all: function(iterator) {
var result = true;
this.each(function(value, index) {
result = result && !!(iterator || Prototype.K)(value, index);
if (!result) throw $break;
});
return result;
},
any: function(iterator) {
var result = false;
this.each(function(value, index) {
if (result = !!(iterator || Prototype.K)(value, index))
throw $break;
});
return result;
},
collect: function(iterator) {
var results = [];
this.each(function(value, index) {
results.push((iterator || Prototype.K)(value, index));
});
return results;
},
detect: function(iterator) {
var result;
this.each(function(value, index) {
if (iterator(value, index)) {
result = value;
throw $break;
}
});
return result;
},
findAll: function(iterator) {
var results = [];
this.each(function(value, index) {
if (iterator(value, index))
results.push(value);
});
return results;
},
grep: function(pattern, iterator) {
var results = [];
this.each(function(value, index) {
var stringValue = value.toString();
if (stringValue.match(pattern))
results.push((iterator || Prototype.K)(value, index));
})
return results;
},
include: function(object) {
var found = false;
this.each(function(value) {
if (value == object) {
found = true;
throw $break;
}
});
return found;
},
inGroupsOf: function(number, fillWith) {
fillWith = fillWith === undefined ? null : fillWith;
return this.eachSlice(number, function(slice) {
while(slice.length < number) slice.push(fillWith);
return slice;
});
},
inject: function(memo, iterator) {
this.each(function(value, index) {
memo = iterator(memo, value, index);
});
return memo;
},
invoke: function(method) {
var args = $A(arguments).slice(1);
return this.map(function(value) {
return value[method].apply(value, args);
});
},
max: function(iterator) {
var result;
this.each(function(value, index) {
value = (iterator || Prototype.K)(value, index);
if (result == undefined || value >= result)
result = value;
});
return result;
},
min: function(iterator) {
var result;
this.each(function(value, index) {
value = (iterator || Prototype.K)(value, index);
if (result == undefined || value < result)
result = value;
});
return result;
},
partition: function(iterator) {
var trues = [], falses = [];
this.each(function(value, index) {
((iterator || Prototype.K)(value, index) ?
trues : falses).push(value);
});
return [trues, falses];
},
pluck: function(property) {
var results = [];
this.each(function(value, index) {
results.push(value[property]);
});
return results;
},
reject: function(iterator) {
var results = [];
this.each(function(value, index) {
if (!iterator(value, index))
results.push(value);
});
return results;
},
sortBy: function(iterator) {
return this.map(function(value, index) {
return {value: value, criteria: iterator(value, index)};
}).sort(function(left, right) {
var a = left.criteria, b = right.criteria;
return a < b ? -1 : a > b ? 1 : 0;
}).pluck('value');
},
toArray: function() {
return this.map();
},
zip: function() {
var iterator = Prototype.K, args = $A(arguments);
if (typeof args.last() == 'function')
iterator = args.pop();
var collections = [this].concat(args).map($A);
return this.map(function(value, index) {
return iterator(collections.pluck(index));
});
},
size: function() {
return this.toArray().length;
},
inspect: function() {
return '#<Enumerable:' + this.toArray().inspect() + '>';
}
}
Object.extend(Enumerable, {
map:     Enumerable.collect,
find:    Enumerable.detect,
select:  Enumerable.findAll,
member:  Enumerable.include,
entries: Enumerable.toArray
});
var $A = Array.from = function(iterable) {
if (!iterable) return [];
if (iterable.toArray) {
return iterable.toArray();
} else {
var results = [];
for (var i = 0, length = iterable.length; i < length; i++)
results.push(iterable[i]);
return results;
}
}
Object.extend(Array.prototype, Enumerable);
if (!Array.prototype._reverse)
Array.prototype._reverse = Array.prototype.reverse;
Object.extend(Array.prototype, {
_each: function(iterator) {
for (var i = 0, length = this.length; i < length; i++)
iterator(this[i]);
},
clear: function() {
this.length = 0;
return this;
},
first: function() {
return this[0];
},
last: function() {
return this[this.length - 1];
},
compact: function() {
return this.select(function(value) {
return value != null;
});
},
flatten: function() {
return this.inject([], function(array, value) {
return array.concat(value && value.constructor == Array ?
value.flatten() : [value]);
});
},
without: function() {
var values = $A(arguments);
return this.select(function(value) {
return !values.include(value);
});
},
indexOf: function(object) {
for (var i = 0, length = this.length; i < length; i++)
if (this[i] == object) return i;
return -1;
},
reverse: function(inline) {
return (inline !== false ? this : this.toArray())._reverse();
},
reduce: function() {
return this.length > 1 ? this : this[0];
},
uniq: function() {
return this.inject([], function(array, value) {
return array.include(value) ? array : array.concat([value]);
});
},
clone: function() {
return [].concat(this);
},
size: function() {
return this.length;
},
inspect: function() {
return '[' + this.map(Object.inspect).join(', ') + ']';
}
});
Array.prototype.toArray = Array.prototype.clone;
function $w(string){
string = string.strip();
return string ? string.split(/\s+/) : [];
}
if(window.opera){
Array.prototype.concat = function(){
var array = [];
for(var i = 0, length = this.length; i < length; i++) array.push(this[i]);
for(var i = 0, length = arguments.length; i < length; i++) {
if(arguments[i].constructor == Array) {
for(var j = 0, arrayLength = arguments[i].length; j < arrayLength; j++)
array.push(arguments[i][j]);
} else {
array.push(arguments[i]);
}
}
return array;
}
}
var Hash = function(obj) {
Object.extend(this, obj || {});
};
Object.extend(Hash, {
toQueryString: function(obj) {
var parts = [];
this.prototype._each.call(obj, function(pair) {
if (!pair.key) return;
if (pair.value && pair.value.constructor == Array) {
var values = pair.value.compact();
if (values.length < 2) pair.value = values.reduce();
else {
key = encodeURIComponent(pair.key);
values.each(function(value) {
value = value != undefined ? encodeURIComponent(value) : '';
parts.push(key + '=' + value);
});
return;
}
}
if (pair.value == undefined) pair[1] = '';
parts.push(pair.map(encodeURIComponent).join('='));
});
return parts.join('&');
}
});
Object.extend(Hash.prototype, Enumerable);
Object.extend(Hash.prototype, {
_each: function(iterator) {
for (var key in this) {
var value = this[key];
if (value && value == Hash.prototype[key]) continue;
var pair = [key, value];
pair.key = key;
pair.value = value;
iterator(pair);
}
},
keys: function() {
return this.pluck('key');
},
values: function() {
return this.pluck('value');
},
merge: function(hash) {
return $H(hash).inject(this, function(mergedHash, pair) {
mergedHash[pair.key] = pair.value;
return mergedHash;
});
},
remove: function() {
var result;
for(var i = 0, length = arguments.length; i < length; i++) {
var value = this[arguments[i]];
if (value !== undefined){
if (result === undefined) result = value;
else {
if (result.constructor != Array) result = [result];
result.push(value)
}
}
delete this[arguments[i]];
}
return result;
},
toQueryString: function() {
return Hash.toQueryString(this);
},
inspect: function() {
return '#<Hash:{' + this.map(function(pair) {
return pair.map(Object.inspect).join(': ');
}).join(', ') + '}>';
}
});
function $H(object) {
if (object && object.constructor == Hash) return object;
return new Hash(object);
};
ObjectRange = Class.create();
Object.extend(ObjectRange.prototype, Enumerable);
Object.extend(ObjectRange.prototype, {
initialize: function(start, end, exclusive) {
this.start = start;
this.end = end;
this.exclusive = exclusive;
},
_each: function(iterator) {
var value = this.start;
while (this.include(value)) {
iterator(value);
value = value.succ();
}
},
include: function(value) {
if (value < this.start)
return false;
if (this.exclusive)
return value < this.end;
return value <= this.end;
}
});
var $R = function(start, end, exclusive) {
return new ObjectRange(start, end, exclusive);
}
var Ajax = {
getTransport: function() {
return Try.these(
function() {return new XMLHttpRequest()},
function() {return new ActiveXObject('Msxml2.XMLHTTP')},
function() {return new ActiveXObject('Microsoft.XMLHTTP')}
) || false;
},
activeRequestCount: 0
}
Ajax.Responders = {
responders: [],
_each: function(iterator) {
this.responders._each(iterator);
},
register: function(responder) {
if (!this.include(responder))
this.responders.push(responder);
},
unregister: function(responder) {
this.responders = this.responders.without(responder);
},
dispatch: function(callback, request, transport, json) {
this.each(function(responder) {
if (typeof responder[callback] == 'function') {
try {
responder[callback].apply(responder, [request, transport, json]);
} catch (e) {}
}
});
}
};
Object.extend(Ajax.Responders, Enumerable);
Ajax.Responders.register({
onCreate: function() {
Ajax.activeRequestCount++;
},
onComplete: function() {
Ajax.activeRequestCount--;
}
});
Ajax.Base = function() {};
Ajax.Base.prototype = {
setOptions: function(options) {
this.options = {
method:       'post',
asynchronous: true,
contentType:  'application/x-www-form-urlencoded',
encoding:     'UTF-8',
parameters:   ''
}
Object.extend(this.options, options || {});
this.options.method = this.options.method.toLowerCase();
if (typeof this.options.parameters == 'string')
this.options.parameters = this.options.parameters.toQueryParams();
}
}
Ajax.Request = Class.create();
Ajax.Request.Events =
['Uninitialized', 'Loading', 'Loaded', 'Interactive', 'Complete'];
Ajax.Request.prototype = Object.extend(new Ajax.Base(), {
_complete: false,
initialize: function(url, options) {
this.transport = Ajax.getTransport();
this.setOptions(options);
this.request(url);
},
request: function(url) {
this.url = url;
this.method = this.options.method;
var params = this.options.parameters;
if (!['get', 'post'].include(this.method)) {
params['_method'] = this.method;
this.method = 'post';
}
params = Hash.toQueryString(params);
if (params && /Konqueror|Safari|KHTML/.test(navigator.userAgent)) params += '&_=';
if (this.method == 'get' && params)
this.url += (this.url.indexOf('?') > -1 ? '&' : '?') + params;
try {
Ajax.Responders.dispatch('onCreate', this, this.transport);
this.transport.open(this.method.toUpperCase(), this.url,
this.options.asynchronous);
if (this.options.asynchronous)
setTimeout(function() { this.respondToReadyState(1) }.bind(this), 10);
this.transport.onreadystatechange = this.onStateChange.bind(this);
this.setRequestHeaders();
var body = this.method == 'post' ? (this.options.postBody || params) : null;
this.transport.send(body);
if (!this.options.asynchronous && this.transport.overrideMimeType)
this.onStateChange();
}
catch (e) {
this.dispatchException(e);
}
},
onStateChange: function() {
var readyState = this.transport.readyState;
if (readyState > 1 && !((readyState == 4) && this._complete))
this.respondToReadyState(this.transport.readyState);
},
setRequestHeaders: function() {
var headers = {
'X-Requested-With': 'XMLHttpRequest',
'X-Prototype-Version': Prototype.Version,
'Accept': 'text/javascript, text/html, application/xml, text/xml, */*'
};
if (this.method == 'post') {
headers['Content-type'] = this.options.contentType +
(this.options.encoding ? '; charset=' + this.options.encoding : '');
if (this.transport.overrideMimeType &&
(navigator.userAgent.match(/Gecko\/(\d{4})/) || [0,2005])[1] < 2005)
headers['Connection'] = 'close';
}
if (typeof this.options.requestHeaders == 'object') {
var extras = this.options.requestHeaders;
if (typeof extras.push == 'function')
for (var i = 0, length = extras.length; i < length; i += 2)
headers[extras[i]] = extras[i+1];
else
$H(extras).each(function(pair) { headers[pair.key] = pair.value });
}
for (var name in headers)
this.transport.setRequestHeader(name, headers[name]);
},
success: function() {
return !this.transport.status
|| (this.transport.status >= 200 && this.transport.status < 300);
},
respondToReadyState: function(readyState) {
var state = Ajax.Request.Events[readyState];
var transport = this.transport, json = this.evalJSON();
if (state == 'Complete') {
try {
this._complete = true;
(this.options['on' + this.transport.status]
|| this.options['on' + (this.success() ? 'Success' : 'Failure')]
|| Prototype.emptyFunction)(transport, json);
} catch (e) {
this.dispatchException(e);
}
if (! this.options['noautoeval']){
if ((this.getHeader('Content-type') || 'text/javascript').strip().
match(/^(text|application)\/(x-)?(java|ecma)script(;.*)?$/i))
this.evalResponse();
}
}
try {
(this.options['on' + state] || Prototype.emptyFunction)(transport, json);
Ajax.Responders.dispatch('on' + state, this, transport, json);
} catch (e) {
this.dispatchException(e);
}
if (state == 'Complete') {
try {this.transport.onreadystatechange = Prototype.emptyFunction;} catch (e) {}
}
},
getHeader: function(name) {
try {
return this.transport.getResponseHeader(name);
} catch (e) { return null }
},
evalJSON: function() {
try {
var json = this.getHeader('X-JSON');
return json ? eval('(' + json + ')') : null;
} catch (e) { return null }
},
evalResponse: function() {
try {
return eval(this.transport.responseText);
} catch (e) {
this.dispatchException(e);
}
},
dispatchException: function(exception) {
(this.options.onException || Prototype.emptyFunction)(this, exception);
Ajax.Responders.dispatch('onException', this, exception);
}
});
Ajax.Updater = Class.create();
Object.extend(Object.extend(Ajax.Updater.prototype, Ajax.Request.prototype), {
initialize: function(container, url, options) {
this.container = {
success: (container.success || container),
failure: (container.failure || (container.success ? null : container))
}
this.transport = Ajax.getTransport();
this.setOptions(options);
var onComplete = this.options.onComplete || Prototype.emptyFunction;
this.options.onComplete = (function(transport, param) {
this.updateContent();
onComplete(transport, param);
}).bind(this);
this.request(url);
},
updateContent: function() {
var receiver = this.container[this.success() ? 'success' : 'failure'];
var response = this.transport.responseText;
if (!this.options.evalScripts) response = response.stripScripts();
if (receiver = $(receiver)) {
if (this.options.insertion)
new this.options.insertion(receiver, response);
else
receiver.update(response);
}
if (this.success()) {
if (this.onComplete)
setTimeout(this.onComplete.bind(this), 10);
}
}
});
Ajax.PeriodicalUpdater = Class.create();
Ajax.PeriodicalUpdater.prototype = Object.extend(new Ajax.Base(), {
initialize: function(container, url, options) {
this.setOptions(options);
this.onComplete = this.options.onComplete;
this.frequency = (this.options.frequency || 2);
this.decay = (this.options.decay || 1);
this.updater = {};
this.container = container;
this.url = url;
this.start();
},
start: function() {
this.options.onComplete = this.updateComplete.bind(this);
this.onTimerEvent();
},
stop: function() {
this.updater.options.onComplete = undefined;
clearTimeout(this.timer);
(this.onComplete || Prototype.emptyFunction).apply(this, arguments);
},
updateComplete: function(request) {
if (this.options.decay) {
this.decay = (request.responseText == this.lastText ?
this.decay * this.options.decay : 1);
this.lastText = request.responseText;
}
this.timer = setTimeout(this.onTimerEvent.bind(this),
this.decay * this.frequency * 1000);
},
onTimerEvent: function() {
this.updater = new Ajax.Updater(this.container, this.url, this.options);
}
});
function $(element) {
if (arguments.length > 1) {
for (var i = 0, elements = [], length = arguments.length; i < length; i++)
elements.push($(arguments[i]));
return elements;
}
if (typeof element == 'string')
element = document.getElementById(element);
return Element.extend(element);
}
if (Prototype.BrowserFeatures.XPath) {
document._getElementsByXPath = function(expression, parentElement) {
var results = [];
var query = document.evaluate(expression, $(parentElement) || document,
null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);
for (var i = 0, length = query.snapshotLength; i < length; i++)
results.push(query.snapshotItem(i));
return results;
};
}
document.getElementsByClassName = function(className, parentElement) {
if (Prototype.BrowserFeatures.XPath) {
var q = ".//*[contains(concat(' ', @class, ' '), ' " + className + " ')]";
return document._getElementsByXPath(q, parentElement);
} else {
var children = ($(parentElement) || document.body).getElementsByTagName('*');
var elements = [], child;
for (var i = 0, length = children.length; i < length; i++) {
child = children[i];
if (Element.hasClassName(child, className))
elements.push(Element.extend(child));
}
return elements;
}
};
if (!window.Element)
var Element = new Object();
Element.extend = function(element) {
if (!element || _nativeExtensions || element.nodeType == 3) return element;
if (!element._extended && element.tagName && element != window) {
var methods = Object.clone(Element.Methods), cache = Element.extend.cache;
if (element.tagName == 'FORM')
Object.extend(methods, Form.Methods);
if (['INPUT', 'TEXTAREA', 'SELECT'].include(element.tagName))
Object.extend(methods, Form.Element.Methods);
Object.extend(methods, Element.Methods.Simulated);
for (var property in methods) {
var value = methods[property];
if (typeof value == 'function' && !(property in element))
element[property] = cache.findOrStore(value);
}
}
element._extended = true;
return element;
};
Element.extend.cache = {
findOrStore: function(value) {
return this[value] = this[value] || function() {
return value.apply(null, [this].concat($A(arguments)));
}
}
};
Element.Methods = {
visible: function(element) {
return $(element).style.display != 'none';
},
toggle: function(element) {
element = $(element);
Element[Element.visible(element) ? 'hide' : 'show'](element);
return element;
},
hide: function(element) {
$(element).style.display = 'none';
return element;
},
show: function(element) {
$(element).style.display = '';
return element;
},
remove: function(element) {
element = $(element);
if(element) {
var formList = element.getElementsByTagName("form");
for (var i = 0; i < formList.length; i++){
formList[i].innerHTML = "";
}
}
if(element.parentNode)
element.parentNode.removeChild(element);
return element;
},
update: function(element, html) {
html = typeof html == 'undefined' ? '' : html.toString();
$(element).innerHTML = html.stripScripts();
setTimeout(function() {html.evalScripts()}, 10);
return element;
},
replace: function(element, html) {
element = $(element);
html = typeof html == 'undefined' ? '' : html.toString();
if (element.outerHTML) {
element.outerHTML = html.stripScripts();
} else {
var range = element.ownerDocument.createRange();
range.selectNodeContents(element);
element.parentNode.replaceChild(
range.createContextualFragment(html.stripScripts()), element);
}
setTimeout(function() {html.evalScripts()}, 10);
return element;
},
inspect: function(element) {
element = $(element);
var result = '<' + element.tagName.toLowerCase();
$H({'id': 'id', 'className': 'class'}).each(function(pair) {
var property = pair.first(), attribute = pair.last();
var value = (element[property] || '').toString();
if (value) result += ' ' + attribute + '=' + value.inspect(true);
});
return result + '>';
},
recursivelyCollect: function(element, property) {
element = $(element);
var elements = [];
while (element = element[property])
if (element.nodeType == 1)
elements.push(Element.extend(element));
return elements;
},
ancestors: function(element) {
return $(element).recursivelyCollect('parentNode');
},
descendants: function(element) {
return $A($(element).getElementsByTagName('*'));
},
immediateDescendants: function(element) {
if (!(element = $(element).firstChild)) return [];
while (element && element.nodeType != 1) element = element.nextSibling;
if (element) return [element].concat($(element).nextSiblings());
return [];
},
previousSiblings: function(element) {
return $(element).recursivelyCollect('previousSibling');
},
nextSiblings: function(element) {
return $(element).recursivelyCollect('nextSibling');
},
siblings: function(element) {
element = $(element);
return element.previousSiblings().reverse().concat(element.nextSiblings());
},
match: function(element, selector) {
if (typeof selector == 'string')
selector = new Selector(selector);
return selector.match($(element));
},
up: function(element, expression, index) {
return Selector.findElement($(element).ancestors(), expression, index);
},
down: function(element, expression, index) {
return Selector.findElement($(element).descendants(), expression, index);
},
previous: function(element, expression, index) {
return Selector.findElement($(element).previousSiblings(), expression, index);
},
next: function(element, expression, index) {
return Selector.findElement($(element).nextSiblings(), expression, index);
},
getElementsBySelector: function() {
var args = $A(arguments), element = $(args.shift());
return Selector.findChildElements(element, args);
},
getElementsByClassName: function(element, className) {
return document.getElementsByClassName(className, element);
},
readAttribute: function(element, name) {
if (typeof element == 'string') {
element = $(element);
}
if (!(browser.isIE || browser.isOpera)) {
var t = Element._attributeTranslations;
if (t.values[name]) return t.values[name](element, name);
if (t.names[name])  name = t.names[name];
var attribute = element.attributes[name];
if(attribute) return attribute.nodeValue;
}
return element.getAttribute(name);
},
getHeight: function(element) {
return $(element).getDimensions().height;
},
getWidth: function(element) {
return $(element).getDimensions().width;
},
classNames: function(element) {
return new Element.ClassNames(element);
},
hasClassName: function(element, className) {
if (!(element = $(element))) return;
var elementClassName = element.className;
if (elementClassName == undefined || elementClassName.length == 0) return false;
if (elementClassName == className ||
elementClassName.match(new RegExp("(^|\\s)" + className + "(\\s|$)")))
return true;
return false;
},
addClassName: function(element, className) {
if (!(element = $(element))) return;
Element.classNames(element).add(className);
return element;
},
removeClassName: function(element, className) {
if (!(element = $(element))) return;
Element.classNames(element).remove(className);
return element;
},
toggleClassName: function(element, className) {
if (!(element = $(element))) return;
Element.classNames(element)[element.hasClassName(className) ? 'remove' : 'add'](className);
return element;
},
observe: function() {
Event.observe.apply(Event, arguments);
return $A(arguments).first();
},
stopObserving: function() {
Event.stopObserving.apply(Event, arguments);
return $A(arguments).first();
},
cleanWhitespace: function(element) {
element = $(element);
var node = element.firstChild;
while (node) {
var nextNode = node.nextSibling;
if (node.nodeType == 3 && !/\S/.test(node.nodeValue))
element.removeChild(node);
node = nextNode;
}
return element;
},
empty: function(element) {
return $(element).innerHTML.match(/^\s*$/);
},
descendantOf: function(element, ancestor) {
element = $(element), ancestor = $(ancestor);
while (element = element.parentNode)
if (element == ancestor) return true;
return false;
},
scrollTo: function(element) {
element = $(element);
var pos = Position.cumulativeOffset(element);
window.scrollTo(pos[0], pos[1]);
return element;
},
getStyle: function(element, style) {
if (typeof element == 'string') {
element = $(element);
}
if (['float','cssFloat'].include(style))
style = (typeof element.style.styleFloat != 'undefined' ? 'styleFloat' : 'cssFloat');
style = style.camelize();
var value = element.style[style];
if (!value) {
if (document.defaultView && document.defaultView.getComputedStyle) {
var css = document.defaultView.getComputedStyle(element, null);
if(css) {
if(css[style] == undefined) {
value = css.getPropertyValue(style);
} else {
value = css[style];
}
} else {
value = null;
}
} else if (element.currentStyle) {
value = element.currentStyle[style];
if(value == "thin") {
value = "2px";
} else if(value == "medium") {
value = "4px";
} else if(value == "thick") {
value = "6px";
}
}
}
if((value == 'auto') && ['width','height'].include(style) && (Element.getStyle(element, 'display') != 'none'))
value = element['offset'+style.capitalize()] + 'px';
if (window.opera && ['left', 'top', 'right', 'bottom'].include(style))
if (Element.getStyle(element, 'position') == 'static') value = 'auto';
if(style == 'opacity') {
if(value) return parseFloat(value);
if(value = (element.getStyle('filter') || '').match(/alpha\(opacity=(.*)\)/))
if(value[1]) return parseFloat(value[1]) / 100;
return 1.0;
}
return value == 'auto' ? null : value;
},
setStyle: function(element, style) {
if (typeof element == 'string') {
element = $(element);
}
for (var name in style) {
var value = style[name];
if(name == 'opacity') {
if (value == 1) {
value = (/Gecko/.test(navigator.userAgent) &&
!/Konqueror|Safari|KHTML/.test(navigator.userAgent)) ? 0.999999 : 1.0;
if(/MSIE/.test(navigator.userAgent) && !window.opera)
element.style.filter = element.getStyle('filter').replace(/alpha\([^\)]*\)/gi,'');
} else if(value == '') {
if(/MSIE/.test(navigator.userAgent) && !window.opera)
element.style.filter = element.getStyle('filter').replace(/alpha\([^\)]*\)/gi,'');
} else {
if(value < 0.00001) value = 0;
if(/MSIE/.test(navigator.userAgent) && !window.opera)
element.style.filter = Element.getStyle(element, 'filter').replace(/alpha\([^\)]*\)/gi,'') +
'alpha(opacity='+value*100+')';
}
} else if(['float','cssFloat'].include(name)) name = (typeof element.style.styleFloat != 'undefined') ? 'styleFloat' : 'cssFloat';
element.style[name.camelize()] = value;
if(browser.isNS && name.camelize() == 'opacity') {
element.style.MozOpacity = value;
}
}
return element;
},
getDimensions: function(element) {
element = $(element);
var display = $(element).getStyle('display');
if (display != 'none' && display != null)
return {width: element.offsetWidth, height: element.offsetHeight};
var els = element.style;
var originalVisibility = els.visibility;
var originalPosition = els.position;
var originalDisplay = els.display;
els.visibility = 'hidden';
els.position = 'absolute';
els.display = 'block';
var originalWidth = element.clientWidth;
var originalHeight = element.clientHeight;
els.display = originalDisplay;
els.position = originalPosition;
els.visibility = originalVisibility;
return {width: originalWidth, height: originalHeight};
},
makePositioned: function(element) {
element = $(element);
var pos = Element.getStyle(element, 'position');
if (pos == 'static' || !pos) {
element._madePositioned = true;
element.style.position = 'relative';
if (window.opera) {
element.style.top = 0;
element.style.left = 0;
}
}
return element;
},
undoPositioned: function(element) {
element = $(element);
if (element._madePositioned) {
element._madePositioned = undefined;
element.style.position =
element.style.top =
element.style.left =
element.style.bottom =
element.style.right = '';
}
return element;
},
makeClipping: function(element) {
element = $(element);
if (element._overflow) return element;
element._overflow = element.style.overflow || 'auto';
if ((Element.getStyle(element, 'overflow') || 'visible') != 'hidden')
element.style.overflow = 'hidden';
return element;
},
undoClipping: function(element) {
element = $(element);
if (!element._overflow) return element;
element.style.overflow = element._overflow == 'auto' ? '' : element._overflow;
element._overflow = null;
return element;
},
getParentElement: function(object,nodeNum) {
if(nodeNum == undefined) {
nodeNum = 1;
}
while(nodeNum > 0){
if(object.parentNode)
object = object.parentNode;
nodeNum = nodeNum - 1;
}
return object;
},
getChildElement: function(object,nodeNum) {
if(nodeNum == undefined) {
nodeNum = 1;
}
while(nodeNum > 0){
if(object.childNodes[0]){
object = object.childNodes[0];
if(object && object.tagName == undefined) {
while(object.tagName == undefined){
object = object.nextSibling;
if(!object)
return null;
}
}
}else
return null;
nodeNum = nodeNum - 1;
}
return object;
},
getChildElementByClassName: function(el, className) {
var el = $(el);
if(Element.hasClassName(el, className)) {
return el;
}
var tmp = null;
for (var i = 0; i < el.childNodes.length; i++) {
if(el.childNodes[i].nodeType == 1) {
tmp = this.getChildElementByClassName(el.childNodes[i], className);
if (tmp != null)
return tmp;
}
}
return null;
},
getParentElementByClassName:  function(el, className) {
var el = $(el);
if(Element.hasClassName(el, className)) {
return el;
}
el = el.parentNode;
if(!el || el.tagName == "BODY")
return null;
var tmp = this.getParentElementByClassName(el, className);
if(tmp != null)
return tmp
return null;
}
};
Object.extend(Element.Methods, {childOf: Element.Methods.descendantOf});
Element._attributeTranslations = {};
Element._attributeTranslations.names = {
colspan:   "colSpan",
rowspan:   "rowSpan",
valign:    "vAlign",
datetime:  "dateTime",
accesskey: "accessKey",
tabindex:  "tabIndex",
enctype:   "encType",
maxlength: "maxLength",
readonly:  "readOnly",
longdesc:  "longDesc"
};
Element._attributeTranslations.values = {
_getAttr: function(element, attribute) {
return element.getAttribute(attribute, 2);
},
_flag: function(element, attribute) {
return $(element).hasAttribute(attribute) ? attribute : null;
},
style: function(element) {
return element.style.cssText.toLowerCase();
},
title: function(element) {
var node = element.getAttributeNode('title');
return node.specified ? node.nodeValue : null;
}
};
Object.extend(Element._attributeTranslations.values, {
href: Element._attributeTranslations.values._getAttr,
src:  Element._attributeTranslations.values._getAttr,
disabled: Element._attributeTranslations.values._flag,
checked:  Element._attributeTranslations.values._flag,
readonly: Element._attributeTranslations.values._flag,
multiple: Element._attributeTranslations.values._flag
});
Element.Methods.Simulated = {
hasAttribute: function(element, attribute) {
var t = Element._attributeTranslations;
attribute = t.names[attribute] || attribute;
return $(element).getAttributeNode(attribute).specified;
}
};
if (document.all && !window.opera){
Element.Methods.update = function(element, html) {
element = $(element);
html = typeof html == 'undefined' ? '' : html.toString();
var tagName = element.tagName.toUpperCase();
if (['THEAD','TBODY','TR','TD'].include(tagName)) {
var div = document.createElement('div');
switch (tagName) {
case 'THEAD':
case 'TBODY':
div.innerHTML = '<table><tbody>' +  html.stripScripts() + '</tbody></table>';
depth = 2;
break;
case 'TR':
div.innerHTML = '<table><tbody><tr>' +  html.stripScripts() + '</tr></tbody></table>';
depth = 3;
break;
case 'TD':
div.innerHTML = '<table><tbody><tr><td>' +  html.stripScripts() + '</td></tr></tbody></table>';
depth = 4;
}
$A(element.childNodes).each(function(node){
element.removeChild(node)
});
depth.times(function(){ div = div.firstChild });
$A(div.childNodes).each(
function(node){ element.appendChild(node) });
} else {
element.innerHTML = html.stripScripts();
}
setTimeout(function() {html.evalScripts()}, 10);
return element;
}
};
Object.extend(Element, Element.Methods);
var _nativeExtensions = false;
if(/Konqueror|Safari|KHTML/.test(navigator.userAgent))
['', 'Form', 'Input', 'TextArea', 'Select'].each(function(tag) {
var className = 'HTML' + tag + 'Element';
if(window[className]) return;
var klass = window[className] = {};
klass.prototype = document.createElement(tag ? tag.toLowerCase() : 'div').__proto__;
});
Element.addMethods = function(methods) {
Object.extend(Element.Methods, methods || {});
function copy(methods, destination, onlyIfAbsent) {
onlyIfAbsent = onlyIfAbsent || false;
var cache = Element.extend.cache;
for (var property in methods) {
var value = methods[property];
if (!onlyIfAbsent || !(property in destination))
destination[property] = cache.findOrStore(value);
}
}
if (typeof HTMLElement != 'undefined') {
copy(Element.Methods, HTMLElement.prototype);
copy(Element.Methods.Simulated, HTMLElement.prototype, true);
copy(Form.Methods, HTMLFormElement.prototype);
[HTMLInputElement, HTMLTextAreaElement, HTMLSelectElement].each(function(klass) {
copy(Form.Element.Methods, klass.prototype);
});
_nativeExtensions = true;
}
}
var Toggle = new Object();
Toggle.display = Element.toggle;
Abstract.Insertion = function(adjacency) {
this.adjacency = adjacency;
}
Abstract.Insertion.prototype = {
initialize: function(element, content) {
this.element = $(element);
this.content = content.stripScripts();
if (this.adjacency && this.element.insertAdjacentHTML) {
try {
this.element.insertAdjacentHTML(this.adjacency, this.content);
} catch (e) {
var tagName = this.element.tagName.toUpperCase();
if (['TBODY', 'TR'].include(tagName)) {
this.insertContent(this.contentFromAnonymousTable());
} else {
throw e;
}
}
} else {
this.range = this.element.ownerDocument.createRange();
if (this.initializeRange) this.initializeRange();
this.insertContent([this.range.createContextualFragment(this.content)]);
}
setTimeout(function() {content.evalScripts()}, 10);
},
contentFromAnonymousTable: function() {
var div = document.createElement('div');
div.innerHTML = '<table><tbody>' + this.content + '</tbody></table>';
return $A(div.childNodes[0].childNodes[0].childNodes);
}
}
var Insertion = new Object();
Insertion.Before = Class.create();
Insertion.Before.prototype = Object.extend(new Abstract.Insertion('beforeBegin'), {
initializeRange: function() {
this.range.setStartBefore(this.element);
},
insertContent: function(fragments) {
fragments.each((function(fragment) {
this.element.parentNode.insertBefore(fragment, this.element);
}).bind(this));
}
});
Insertion.Top = Class.create();
Insertion.Top.prototype = Object.extend(new Abstract.Insertion('afterBegin'), {
initializeRange: function() {
this.range.selectNodeContents(this.element);
this.range.collapse(true);
},
insertContent: function(fragments) {
fragments.reverse(false).each((function(fragment) {
this.element.insertBefore(fragment, this.element.firstChild);
}).bind(this));
}
});
Insertion.Bottom = Class.create();
Insertion.Bottom.prototype = Object.extend(new Abstract.Insertion('beforeEnd'), {
initializeRange: function() {
this.range.selectNodeContents(this.element);
this.range.collapse(this.element);
},
insertContent: function(fragments) {
fragments.each((function(fragment) {
this.element.appendChild(fragment);
}).bind(this));
}
});
Insertion.After = Class.create();
Insertion.After.prototype = Object.extend(new Abstract.Insertion('afterEnd'), {
initializeRange: function() {
this.range.setStartAfter(this.element);
},
insertContent: function(fragments) {
fragments.each((function(fragment) {
this.element.parentNode.insertBefore(fragment,
this.element.nextSibling);
}).bind(this));
}
});
Element.ClassNames = Class.create();
Element.ClassNames.prototype = {
initialize: function(element) {
this.element = $(element);
},
_each: function(iterator) {
this.element.className.split(/\s+/).select(function(name) {
return name.length > 0;
})._each(iterator);
},
set: function(className) {
this.element.className = className;
},
add: function(classNameToAdd) {
if (this.include(classNameToAdd)) return;
this.set($A(this).concat(classNameToAdd).join(' '));
},
remove: function(classNameToRemove) {
if (!this.include(classNameToRemove)) return;
this.set($A(this).without(classNameToRemove).join(' '));
},
toString: function() {
return $A(this).join(' ');
}
};
Object.extend(Element.ClassNames.prototype, Enumerable);
var Selector = Class.create();
Selector.prototype = {
initialize: function(expression) {
this.params = {classNames: []};
this.expression = expression.toString().strip();
this.parseExpression();
this.compileMatcher();
},
parseExpression: function() {
function abort(message) { throw 'Parse error in selector: ' + message; }
if (this.expression == '')  abort('empty expression');
var params = this.params, expr = this.expression, match, modifier, clause, rest;
while (match = expr.match(/^(.*)\[([a-z0-9_:-]+?)(?:([~\|!]?=)(?:"([^"]*)"|([^\]\s]*)))?\]$/i)) {
params.attributes = params.attributes || [];
params.attributes.push({name: match[2], operator: match[3], value: match[4] || match[5] || ''});
expr = match[1];
}
if (expr == '*') return this.params.wildcard = true;
while (match = expr.match(/^([^a-z0-9_-])?([a-z0-9_-]+)(.*)/i)) {
modifier = match[1], clause = match[2], rest = match[3];
switch (modifier) {
case '#':       params.id = clause; break;
case '.':       params.classNames.push(clause); break;
case '':
case undefined: params.tagName = clause.toUpperCase(); break;
default:        abort(expr.inspect());
}
expr = rest;
}
if (expr.length > 0) abort(expr.inspect());
},
buildMatchExpression: function() {
var params = this.params, conditions = [], clause;
if (params.wildcard)
conditions.push('true');
if (clause = params.id)
conditions.push('element.readAttribute("id") == ' + clause.inspect());
if (clause = params.tagName)
conditions.push('element.tagName.toUpperCase() == ' + clause.inspect());
if ((clause = params.classNames).length > 0)
for (var i = 0, length = clause.length; i < length; i++)
conditions.push('element.hasClassName(' + clause[i].inspect() + ')');
if (clause = params.attributes) {
clause.each(function(attribute) {
var value = 'element.readAttribute(' + attribute.name.inspect() + ')';
var splitValueBy = function(delimiter) {
return value + ' && ' + value + '.split(' + delimiter.inspect() + ')';
}
switch (attribute.operator) {
case '=':       conditions.push(value + ' == ' + attribute.value.inspect()); break;
case '~=':      conditions.push(splitValueBy(' ') + '.include(' + attribute.value.inspect() + ')'); break;
case '|=':      conditions.push(
splitValueBy('-') + '.first().toUpperCase() == ' + attribute.value.toUpperCase().inspect()
); break;
case '!=':      conditions.push(value + ' != ' + attribute.value.inspect()); break;
case '':
case undefined: conditions.push('element.hasAttribute(' + attribute.name.inspect() + ')'); break;
default:        throw 'Unknown operator ' + attribute.operator + ' in selector';
}
});
}
return conditions.join(' && ');
},
compileMatcher: function() {
this.match = new Function('element', 'if (!element.tagName) return false; \
element = $(element); \
return ' + this.buildMatchExpression());
},
findElements: function(scope) {
var element;
if (element = $(this.params.id))
if (this.match(element))
if (!scope || Element.childOf(element, scope))
return [element];
scope = (scope || document).getElementsByTagName(this.params.tagName || '*');
var results = [];
for (var i = 0, length = scope.length; i < length; i++)
if (this.match(element = scope[i]))
results.push(Element.extend(element));
return results;
},
toString: function() {
return this.expression;
}
}
Object.extend(Selector, {
matchElements: function(elements, expression) {
var selector = new Selector(expression);
return elements.select(selector.match.bind(selector)).map(Element.extend);
},
findElement: function(elements, expression, index) {
if (typeof expression == 'number') index = expression, expression = false;
return Selector.matchElements(elements, expression || '*')[index || 0];
},
findChildElements: function(element, expressions) {
return expressions.map(function(expression) {
return expression.match(/[^\s"]+(?:"[^"]*"[^\s"]+)*/g).inject([null], function(results, expr) {
var selector = new Selector(expr);
return results.inject([], function(elements, result) {
return elements.concat(selector.findElements(result || element));
});
});
}).flatten();
}
});
function $$() {
return Selector.findChildElements(document, $A(arguments));
}
var Form = {
reset: function(form) {
$(form).reset();
return form;
},
serializeElements: function(elements, getHash) {
var data = elements.inject({}, function(result, element) {
if (!element.disabled && element.name) {
var key = element.name, value = Form.Element.getValue($(element));
if (value != undefined) {
if (result[key]) {
if (result[key].constructor != Array) result[key] = [result[key]];
result[key].push(value);
}
else result[key] = value;
}
}
return result;
});
return getHash ? data : Hash.toQueryString(data);
}
};
Form.Methods = {
serialize: function(form, getHash) {
return Form.serializeElements(Form.getElements(form), getHash);
},
getElements: function(form) {
return $A($(form).getElementsByTagName('*')).inject([],
function(elements, child) {
if (Form.Element.Serializers[child.tagName.toLowerCase()])
elements.push(Element.extend(child));
return elements;
}
);
},
getInputs: function(form, typeName, name) {
form = $(form);
var inputs = form.getElementsByTagName('input');
if (!typeName && !name) return $A(inputs).map(Element.extend);
for (var i = 0, matchingInputs = [], length = inputs.length; i < length; i++) {
var input = inputs[i];
if ((typeName && input.type != typeName) || (name && input.name != name))
continue;
matchingInputs.push(Element.extend(input));
}
return matchingInputs;
},
disable: function(form) {
form = $(form);
form.getElements().each(function(element) {
element.blur();
element.disabled = 'true';
});
return form;
},
enable: function(form) {
form = $(form);
form.getElements().each(function(element) {
element.disabled = '';
});
return form;
},
findFirstElement: function(form) {
return Form.getElements($(form)).find(function(element) {
return element.type != 'hidden' && !element.disabled &&
['input', 'select', 'textarea'].include(element.tagName.toLowerCase());
});
},
focusFirstElement: function(form) {
form = $(form);
var el = Form.findFirstElement(form);
Form.Element.Methods.activate(el);
return form;
}
}
Object.extend(Form, Form.Methods);
Form.Element = {
focus: function(element) {
$(element).focus();
return element;
},
select: function(element) {
$(element).select();
return element;
}
}
Form.Element.Methods = {
serialize: function(element) {
element = $(element);
if (!element.disabled && element.name) {
var value = element.getValue();
if (value != undefined) {
var pair = {};
pair[element.name] = value;
return Hash.toQueryString(pair);
}
}
return '';
},
getValue: function(element) {
element = $(element);
var method = element.tagName.toLowerCase();
return Form.Element.Serializers[method](element);
},
clear: function(element) {
$(element).value = '';
return element;
},
present: function(element) {
return $(element).value != '';
},
activate: function(element) {
element = $(element);
element.focus();
if (element.select && ( element.tagName.toLowerCase() != 'input' ||
!['button', 'reset', 'submit'].include(element.type) ) )
element.select();
return element;
},
disable: function(element) {
element = $(element);
element.disabled = true;
return element;
},
enable: function(element) {
element = $(element);
element.blur();
element.disabled = false;
return element;
}
}
Object.extend(Form.Element, Form.Element.Methods);
var Field = Form.Element;
var $F = Form.Element.getValue;
Form.Element.setValue = function(element,newValue) {
element_id = element;
element = $(element);
if (!element){element = document.getElementsByName(element_id)[0];}
if (!element){return false;}
var method = element.tagName.toLowerCase();
var parameter = Form.Element.SetSerializers[method](element,newValue);
}
Form.Element.SetSerializers = {
input: function(element,newValue) {
switch (element.type.toLowerCase()) {
case 'submit':
case 'hidden':
case 'password':
case 'text':
return Form.Element.SetSerializers.textarea(element,newValue);
case 'checkbox':
case 'radio':
return Form.Element.SetSerializers.inputSelector(element,newValue);
}
return false;
},
inputSelector: function(element,newValue) {
fields = document.getElementsByName(element.name);
for (var i=0;i<fields.length;i++){
if (fields[i].value == newValue){
fields[i].checked = true;
}
}
},
textarea: function(element,newValue) {
element.value = newValue;
},
select: function(element,newValue) {
var value = '', opt, index = element.selectedIndex;
for (var i=0;i< element.options.length;i++){
if (element.options[i].value == newValue){
element.selectedIndex = i;
return true;
}
}
}
}
Form.Element.Serializers = {
input: function(element) {
switch (element.type.toLowerCase()) {
case 'checkbox':
case 'radio':
return Form.Element.Serializers.inputSelector(element);
default:
return Form.Element.Serializers.textarea(element);
}
},
inputSelector: function(element) {
return element.checked ? element.value : null;
},
textarea: function(element) {
return element.value;
},
select: function(element) {
return this[element.type == 'select-one' ?
'selectOne' : 'selectMany'](element);
},
selectOne: function(element) {
var index = element.selectedIndex;
return index >= 0 ? this.optionValue(element.options[index]) : null;
},
selectMany: function(element) {
var values, length = element.length;
if (!length) return null;
for (var i = 0, values = []; i < length; i++) {
var opt = element.options[i];
if (opt.selected) values.push(this.optionValue(opt));
}
return values;
},
optionValue: function(opt) {
return Element.extend(opt).hasAttribute('value') ? opt.value : opt.text;
}
}
Abstract.TimedObserver = function() {}
Abstract.TimedObserver.prototype = {
initialize: function(element, frequency, callback) {
this.frequency = frequency;
this.element   = $(element);
this.callback  = callback;
this.lastValue = this.getValue();
this.registerCallback();
},
registerCallback: function() {
setInterval(this.onTimerEvent.bind(this), this.frequency * 1000);
},
onTimerEvent: function() {
var value = this.getValue();
var changed = ('string' == typeof this.lastValue && 'string' == typeof value
? this.lastValue != value : String(this.lastValue) != String(value));
if (changed) {
this.callback(this.element, value);
this.lastValue = value;
}
}
}
Form.Element.Observer = Class.create();
Form.Element.Observer.prototype = Object.extend(new Abstract.TimedObserver(), {
getValue: function() {
return Form.Element.getValue(this.element);
}
});
Form.Observer = Class.create();
Form.Observer.prototype = Object.extend(new Abstract.TimedObserver(), {
getValue: function() {
return Form.serialize(this.element);
}
});
Abstract.EventObserver = function() {}
Abstract.EventObserver.prototype = {
initialize: function(element, callback) {
this.element  = $(element);
this.callback = callback;
this.lastValue = this.getValue();
if (this.element.tagName.toLowerCase() == 'form')
this.registerFormCallbacks();
else
this.registerCallback(this.element);
},
onElementEvent: function() {
var value = this.getValue();
if (this.lastValue != value) {
this.callback(this.element, value);
this.lastValue = value;
}
},
registerFormCallbacks: function() {
Form.getElements(this.element).each(this.registerCallback.bind(this));
},
registerCallback: function(element) {
if (element.type) {
switch (element.type.toLowerCase()) {
case 'checkbox':
case 'radio':
Event.observe(element, 'click', this.onElementEvent.bind(this));
break;
default:
Event.observe(element, 'change', this.onElementEvent.bind(this));
break;
}
}
}
}
Form.Element.EventObserver = Class.create();
Form.Element.EventObserver.prototype = Object.extend(new Abstract.EventObserver(), {
getValue: function() {
return Form.Element.getValue(this.element);
}
});
Form.EventObserver = Class.create();
Form.EventObserver.prototype = Object.extend(new Abstract.EventObserver(), {
getValue: function() {
return Form.serialize(this.element);
}
});
if (!window.Event) {
var Event = new Object();
}
Object.extend(Event, {
KEY_BACKSPACE: 8,
KEY_TAB:       9,
KEY_RETURN:   13,
KEY_ESC:      27,
KEY_LEFT:     37,
KEY_UP:       38,
KEY_RIGHT:    39,
KEY_DOWN:     40,
KEY_DELETE:   46,
KEY_HOME:     36,
KEY_END:      35,
KEY_PAGEUP:   33,
KEY_PAGEDOWN: 34,
element: function(event) {
return event.target || event.srcElement;
},
isLeftClick: function(event) {
return (((event.which) && (event.which == 1)) ||
((event.button) && (event.button == 1)));
},
pointerX: function(event) {
return event.pageX || (event.clientX +
(document.documentElement.scrollLeft || document.body.scrollLeft));
},
pointerY: function(event) {
return event.pageY || (event.clientY +
(document.documentElement.scrollTop || document.body.scrollTop));
},
stop: function(event) {
if (event.preventDefault) {
event.preventDefault();
event.stopPropagation();
} else {
event.returnValue = false;
event.cancelBubble = true;
}
},
findElement: function(event, tagName) {
var element = Event.element(event);
while (element.parentNode && (!element.tagName ||
(element.tagName.toUpperCase() != tagName.toUpperCase())))
element = element.parentNode;
return element;
},
observers: false,
_observeAndCache: function(element, name, observer, useCapture, top_el) {
if (name == 'dblclick' &&
navigator.appVersion.match(/Konqueror|Safari|KHTML/) && element.addEventListener) {
this.observers.push([element, name, observer, useCapture, top_el]);
element.ondblclick = observer;
} else {
if (!this.observers) this.observers = [];
if (element.addEventListener) {
this.observers.push([element, name, observer, useCapture, top_el]);
element.addEventListener(name, observer, useCapture);
} else if (element.attachEvent) {
this.observers.push([element, name, observer, useCapture, top_el]);
element.attachEvent('on' + name, observer);
}
}
},
unloadCache: function(top_el) {
top_el = (top_el == undefined || top_el == null || $(top_el).nodeType == undefined) ? undefined : $(top_el);
if (!Event.observers) return;
for (var i = 0; i < Event.observers.length; i++) {
if(top_el == undefined || $(Event.observers[i][4]) == top_el) {
Event._stopObserving.apply(this, Event.observers[i]);
Event.observers[i][0] = null;
if(Event.observers[i][4])
Event.observers[i][4] = null;
}
}
Event.observers = false;
},
observe: function(element, name, observer, useCapture, top_el) {
var element = $(element);
useCapture = useCapture || false;
top_el = top_el || "";
if (name == 'keypress' && !browser.isOpera &&
(navigator.appVersion.match(/Konqueror|Safari|KHTML/)
|| element.attachEvent))
name = 'keydown';
Event._observeAndCache(element, name, observer, useCapture, top_el);
},
_stopObserving: function(element, name, observer, useCapture) {
try {
element = $(element);
} catch (e) {
return;
}
useCapture = useCapture || false;
if (name == 'keypress' && !browser.isOpera &&
(navigator.appVersion.match(/Konqueror|Safari|KHTML/)
|| element.attachEvent))
name = 'keydown';
if (element.removeEventListener) {
element.removeEventListener(name, observer, useCapture);
} else if (element.detachEvent) {
try {
element.detachEvent('on' + name, observer);
} catch (e) {}
}
},
stopObserving: function(element, name, observer, useCapture) {
if (!Event.observers) return;
element = $(element);
if (name == 'keypress' &&
(navigator.appVersion.match(/Konqueror|Safari|KHTML/)
|| element.detachEvent))
name = 'keydown';
var observers = this.observers.partition(function(value){
return (    (!element    || value[0] == element    )
&& (!name       || value[1] == name       )
&& (!observer   || value[2] == observer   )
&& (useCapture == null || value[3] == useCapture )
)
});
if(observers[0].length == 0) return;
observers[0].each(function(value){
this._stopObserving.apply(this, value);
}.bind(this));
this.observers = observers[1];
}
});
if (navigator.appVersion.match(/\bMSIE\b/))
Event.observe(window, 'unload', Event.unloadCache, false);
var Position = {
includeScrollOffsets: false,
prepare: function() {
this.deltaX =  window.pageXOffset
|| document.documentElement.scrollLeft
|| document.body.scrollLeft
|| 0;
this.deltaY =  window.pageYOffset
|| document.documentElement.scrollTop
|| document.body.scrollTop
|| 0;
},
realOffset: function(element) {
var valueT = 0, valueL = 0;
do {
valueT += element.scrollTop  || 0;
valueL += element.scrollLeft || 0;
element = element.parentNode;
} while (element);
return [valueL, valueT];
},
cumulativeOffset: function(element) {
var valueT = 0, valueL = 0;
do {
valueT += element.offsetTop || 0;
valueL += element.offsetLeft || 0;
element = element.offsetParent;
} while (element);
return [valueL, valueT];
},
cumulativeOffsetScroll: function(element) {
var valueT = 0, valueL = 0;
var buf_element = null;
do {
var parent_el = element.parentNode;
if(buf_element == null || buf_element == element) {
valueT += element.offsetTop  || 0;
valueL += element.offsetLeft || 0;
if(parent_el && parent_el.tagName) {
buf_element = element.offsetParent;
} else {
break;
}
}
if(element.tagName != undefined && element.tagName.toLowerCase() != "html" && element.tagName.toLowerCase() != "body") {
valueT -= element.scrollTop  || 0;
valueL -= element.scrollLeft  || 0;
}
element = parent_el;
} while (element);
return [valueL, valueT];
},
positionedOffsetScroll: function(element) {
var valueT = 0, valueL = 0;
var buf_element = null;
do {
if(buf_element == null || buf_element == element) {
valueT += element.offsetTop  || 0;
valueL += element.offsetLeft || 0;
buf_element = element.offsetParent;
}
if(element.tagName != undefined && element.tagName.toLowerCase() != "html" && element.tagName.toLowerCase() != "body") {
valueT -= element.scrollTop  || 0;
valueL -= element.scrollLeft  || 0;
}
element = element.parentNode;
if (element) {
if(element.tagName=='BODY') break;
var p = Element.getStyle(element, 'position');
if (p == 'relative' || p == 'absolute') break;
}
} while (element);
return [valueL, valueT];
},
positionedOffset: function(element) {
var valueT = 0, valueL = 0;
do {
valueT += element.offsetTop  || 0;
valueL += element.offsetLeft || 0;
element = element.offsetParent;
if (element) {
if(element.tagName=='BODY') break;
var p = Element.getStyle(element, 'position');
if (p == 'relative' || p == 'absolute') break;
}
} while (element);
return [valueL, valueT];
},
offsetParent: function(element) {
if (element.offsetParent) return element.offsetParent;
if (element == document.body) return element;
while ((element = element.parentNode) && element != document.body)
if (Element.getStyle(element, 'position') != 'static')
return element;
return document.body;
},
within: function(element, x, y, offset) {
offset = (offset == undefined || offset == null) ? 0 : offset;
if (this.includeScrollOffsets)
return this.withinIncludingScrolloffsets(element, x, y);
this.xcomp = x;
this.ycomp = y;
this.offset = this.cumulativeOffset(element);
return (y >= this.offset[1] + offset &&
y <  this.offset[1] + element.offsetHeight - offset &&
x >= this.offset[0] + offset &&
x <  this.offset[0] + element.offsetWidth - offset);
},
within_x: function(element, x) {
if (this.includeScrollOffsets)
return this.withinIncludingScrolloffsets_x(element, x);
this.xcomp = x;
this.offset = this.cumulativeOffset(element);
return (x >= this.offset[0] &&
x <  this.offset[0] + element.offsetWidth);
},
within_y: function(element, y) {
if (this.includeScrollOffsets)
return this.withinIncludingScrolloffsets_y(element, y);
this.ycomp = y;
this.offset = this.cumulativeOffset(element);
return (y >= this.offset[1] &&
y <  this.offset[1] + element.offsetHeight);
},
withinIncludingScrolloffsets_x: function(element, x) {
var offsetcache = this.realOffset(element);
this.xcomp = x + offsetcache[0] - this.deltaX;
this.offset = this.cumulativeOffset(element);
return (this.xcomp >= this.offset[0] &&
this.xcomp <  this.offset[0] + element.offsetWidth);
},
withinIncludingScrolloffsets_y: function(element, y) {
var offsetcache = this.realOffset(element);
this.ycomp = y + offsetcache[1] - this.deltaY;
this.offset = this.cumulativeOffset(element);
return (this.ycomp >= this.offset[1] &&
this.ycomp <  this.offset[1] + element.offsetHeight);
},
getWinOuterWidth: function(){
if(browser.isSafari)
var offset = 14;
else
var offset = 0;
var width =  document.documentElement.clientWidth
|| document.body.clientWidth
|| 0;
width = width - offset;
return width;
},
getWinOuterHeight: function(){
if(browser.isSafari)
var offset = 65;
else
var offset = 0;
if(browser.isSafari) {
var height =  window.outerHeight;
} else {
var height =  document.documentElement.clientHeight
|| document.body.clientHeight
|| 0;
}
height = height - offset;
return height;
},
getWinOffsetWidth: function(){
var width =  window.offsetWidth
|| document.documentElement.offsetWidth
|| document.body.offsetWidth
|| 0;
return width;
},
getWinOffsetHeight: function(){
var height =  window.offsetHeight
|| document.documentElement.offsetHeight
|| document.body.offsetHeight
|| 0;
return height;
},
withinIncludingScrolloffsets: function(element, x, y) {
var offsetcache = this.realOffset(element);
this.xcomp = x + offsetcache[0] - this.deltaX;
this.ycomp = y + offsetcache[1] - this.deltaY;
this.offset = this.cumulativeOffset(element);
return (this.ycomp >= this.offset[1] &&
this.ycomp <  this.offset[1] + element.offsetHeight &&
this.xcomp >= this.offset[0] &&
this.xcomp <  this.offset[0] + element.offsetWidth);
},
overlap: function(mode, element) {
if (!mode) return 0;
if (mode == 'vertical')
return ((this.offset[1] + element.offsetHeight) - this.ycomp) /
element.offsetHeight;
if (mode == 'horizontal')
return ((this.offset[0] + element.offsetWidth) - this.xcomp) /
element.offsetWidth;
},
page: function(forElement) {
var valueT = 0, valueL = 0;
var element = forElement;
do {
valueT += element.offsetTop  || 0;
valueL += element.offsetLeft || 0;
if (element.offsetParent==document.body)
if (Element.getStyle(element,'position')=='absolute') break;
} while (element = element.offsetParent);
element = forElement;
do {
if (!window.opera || element.tagName=='BODY') {
valueT -= element.scrollTop  || 0;
valueL -= element.scrollLeft || 0;
}
} while (element = element.parentNode);
return [valueL, valueT];
},
clone: function(source, target) {
var options = Object.extend({
setLeft:    true,
setTop:     true,
setWidth:   true,
setHeight:  true,
offsetTop:  0,
offsetLeft: 0
}, arguments[2] || {})
source = $(source);
var p = Position.page(source);
target = $(target);
var delta = [0, 0];
var parent = null;
if (Element.getStyle(target,'position') == 'absolute') {
parent = Position.offsetParent(target);
delta = Position.page(parent);
}
if (parent == document.body) {
delta[0] -= document.body.offsetLeft;
delta[1] -= document.body.offsetTop;
}
if(options.setLeft)   target.style.left  = (p[0] - delta[0] + options.offsetLeft) + 'px';
if(options.setTop)    target.style.top   = (p[1] - delta[1] + options.offsetTop) + 'px';
if(options.setWidth)  target.style.width = source.offsetWidth + 'px';
if(options.setHeight) target.style.height = source.offsetHeight + 'px';
},
absolutize: function(element) {
element = $(element);
if (element.style.position == 'absolute') return;
Position.prepare();
var offsets = Position.positionedOffset(element);
var top     = offsets[1];
var left    = offsets[0];
var width   = element.clientWidth;
var height  = element.clientHeight;
element._originalLeft   = left - parseFloat(element.style.left  || 0);
element._originalTop    = top  - parseFloat(element.style.top || 0);
element._originalWidth  = element.style.width;
element._originalHeight = element.style.height;
element.style.position = 'absolute';
element.style.top    = top + 'px';
element.style.left   = left + 'px';
element.style.width  = width + 'px';
element.style.height = height + 'px';
},
relativize: function(element) {
element = $(element);
if (element.style.position == 'relative') return;
Position.prepare();
element.style.position = 'relative';
var top  = parseFloat(element.style.top  || 0) - (element._originalTop || 0);
var left = parseFloat(element.style.left || 0) - (element._originalLeft || 0);
element.style.top    = top + 'px';
element.style.left   = left + 'px';
element.style.height = element._originalHeight;
element.style.width  = element._originalWidth;
}
}
if (/Konqueror|Safari|KHTML/.test(navigator.userAgent)) {
Position.cumulativeOffset = function(element) {
var valueT = 0, valueL = 0;
do {
valueT += element.offsetTop  || 0;
valueL += element.offsetLeft || 0;
if (element.offsetParent == document.body)
if (Element.getStyle(element, 'position') == 'absolute') break;
element = element.offsetParent;
} while (element);
return [valueL, valueT];
}
}
Element.addMethods();
document._write = document.write;
var _nc_global_script_write_html = '';
var _nc_global_script_span = null;
document._open = document.open;
document.open = function() {};
document.write = function(s) {
_nc_global_script_write_html+=s;
if(_nc_global_script_span == null) {
_nc_global_script_span = document.createElement("SPAN");
}
var dwScriptCount = parseInt(_nc_dwScriptCount);
var dwScriptList = _nc_dwScriptList;
var self = document.write.prototype;
if(_nc_ajaxFlag == true && dwScriptList[dwScriptCount]) {
var current = dwScriptList[dwScriptCount];
} else {
var current = self.getCurrentScript();
}
if(_nc_global_script_span.innerHTML != '') {
if(current){
current.parentNode.insertBefore(_nc_global_script_span, current);
} else {
if (document.body) {
document.body.appendChild(_nc_global_script_span);
}
}
}
_nc_global_script_span.innerHTML = _nc_global_script_write_html;
}
document.write.prototype = {
'getCurrentScript' : function () {
return (
function (el) {
if (el && el.nodeName.toLowerCase() == 'script') return el;
if (!el) return;
return arguments.callee(el.lastChild)
}
)(document);
}
}
function Browser() {
var ua, s, i;
this.isGecko = false;
this.isIE    = false;
this.isNS    = false;
this.isFirefox    = false;
this.isOpera = false;
this.isSafari = false;
this.version = null;
ua = navigator.userAgent;
s = "Opera";
if ((i = ua.indexOf(s)) >= 0) {
this.isOpera = true;
return;
}
s = "MSIE";
if ((i = ua.indexOf(s)) >= 0) {
this.isIE = true;
this.version = parseFloat(ua.substr(i + s.length));
return;
}
s = "Trident";
if ((i = ua.indexOf(s)) >= 0) {
s = "rv:";
i = ua.indexOf(s);
this.isIE = true;
this.version = parseFloat(ua.substr(i + s.length));
return;
}
s = "Netscape";
if ((i = ua.indexOf(s)) >= 0) {
this.isGecko = true;
this.isNS = true;
this.version = parseFloat(ua.substr(i + s.length));
s = "Firefox";
if ((i = ua.indexOf(s)) >= 0) {
this.isFirefox = true;
}
return;
}
s = "Gecko";
if ((i = ua.indexOf(s)) >= 0) {
this.isGecko = true;
this.version = 6.1;
s = "Firefox";
if ((i = ua.indexOf(s)) >= 0) {
this.isFirefox = true;
var version = '';
var version_str = ua.substr(i + s.length);
for (var i = 0; i < version_str.length; i++) {
if(version_str[i].match(/^[0-9\.]/))
version += version_str[i];
}
if(parseFloat(version) > 0)
this.version = parseFloat(version);
}
s = "Safari";
if ((i = ua.indexOf(s)) >= 0) {
this.isSafari = true;
}
return;
}
}
function $_GET($_GETStr,$_URL){
if($_URL == undefined) {
$_GETURL = location.search.split("&");
} else {
$_GETURL = $_URL.split("&");
}
$_GETURL[0] = $_GETURL[0].substr(1,$_GETURL[0].length);
for (var i = 0; i < $_GETURL.length; i++) {
if($_GETStr == $_GETURL[i].substr(0,$_GETURL[i].indexOf("="))){
return decodeURIComponent(
$_GETURL[i].substr($_GETURL[i].indexOf("=")+1,$_GETURL[i].length)
);
}
}
}
var browser = new Browser();
function valueParseInt(param) {
if(param == "" || param == undefined || param == null){
return 0;
}
var ret = parseInt(param, 10);
return isNaN(parseInt(param, 10)) ? 0 : ret;
}
var clsCommon = Class.create();
clsCommon.prototype = {
initialize: function() {
this.moduleList =  new Object();
this.show_x = new Object();
this.show_y = new Object();
this.move_div = new Object();
this.pre_show_x = new Object();
this.pre_show_y = new Object();
this.start_x = new Object();
this.start_y = new Object();
this.speedx = new Object();
this.speedy = new Object();
this.inShowLoading = Array();
this.inModalEvent = Array();
this.max_zIndex = 999;
this.hideElement =  new Object();
this.winMoveDragStartEvent = new Object();
this.winMoveDragGoEvent = new Object();
this.winMoveDragStopEvent = new Object();
this.closeCallbackFuncEvent = new Object();
this.inMoveDrag = new Object();
this.referComp =  new Object();
this.referObject = null;
this.inAttachment = Array();
this.attachmentCallBack = new Object();
this.attachmentErrorCallBack = new Object();
this.attachmentTarget = new Object();
this.error_mes = "error_message:";
this.fatal_error_mes = "^<br \/>\n<b>Fatal error<\/b>:(.|\n|\r|\t)*<\/b>";
this.toolTipPopup   = null;
this.inToolTipEvent = new Object();
this.toolTipPopupTimer = null;
this.sess_timer = null;
this.timeout_time = null;
this.session_timeout_alert = null;
},
commonInit: function(session_timeout_alert, timeout_time) {
var header_menu_el = $("header_menu");
var bodyWidth = Position.getWinOuterWidth();
if(header_menu_el && header_menu_el.offsetWidth > bodyWidth) {
var menu_right_el = Element.getChildElementByClassName(header_menu_el,"menu_right");
if(menu_right_el) {
var header_margin = header_menu_el && header_menu_el.offsetWidth - bodyWidth;
Element.setStyle(menu_right_el, {"paddingRight":header_margin+"px"});
}
}
this.timeout_time = timeout_time*1000 - 60*1000;
this.session_timeout_alert = session_timeout_alert;
this.setTimeoutAlert();
},
setTimeoutAlert: function() {
if(this.sess_timer != null) {
clearTimeout(this.sess_timer);
this.sess_timer = null;
}
if(_nc_user_id != '0') {
this.sess_timer = setTimeout(function(){commonCls.alert(this.session_timeout_alert);}.bind(this), this.timeout_time);
}
},
moduleInit: function(id, chief_flag) {
var el = $(id);
if(!el) return;
var parent_el = Element.getParentElement(el);
var absolute_flag = false;
if(parent_el && !Element.hasClassName(parent_el,"cell")
&& !Element.hasClassName(parent_el,"main_column")
&& parent_el.tagName != "BODY" && !Element.hasClassName(parent_el,"enlarged_display"))
absolute_flag = true;
commonCls.parentWinInit(el, absolute_flag, chief_flag);
},
showLoading: function(id_name, parameters, show_x, show_y, loading_el) {
id_name = (id_name != undefined && id_name != null) ? id_name : "";
parameters = (parameters != undefined && parameters != null) ? parameters : "";
commonCls.hideLoading(id_name, parameters);
var div_parent = document.createElement("DIV");
div_parent.innerHTML = "<div class=\"loading\"><img text=\"loading\" alt=\"loading\" src=\"" + _nc_core_base_url + "/images/common/indicator.gif\"/></div>";
var div = div_parent.childNodes[0];
this.inShowLoading[id_name + parameters] = div_parent;
Element.addClassName(div,"loading");
commonCls.showModal(null,div_parent);
if(loading_el && (show_x==undefined && show_y == undefined)) {
var loading_imege_offset_x = 8;
var loading_imege_offset_y = 8;
var offset = Position.cumulativeOffset(loading_el);
var ex1 = offset[0];
var ey1 = offset[1];
div.style.left = (ex1 + (loading_el.offsetWidth/2) - loading_imege_offset_x) +"px";
div.style.top  = (ey1 + (loading_el.offsetHeight/2) - loading_imege_offset_y) +"px";
} else {
div.style.left = show_x +"px";
div.style.top = show_y +"px";
}
document.body.appendChild(div_parent);
},
hideLoading: function(id_name, parameters) {
id_name = (id_name != undefined && id_name != null) ? id_name : "";
parameters = (parameters != undefined && parameters != null) ? parameters : "";
if(this.inShowLoading[id_name + parameters]) {
commonCls.stopModal(this.inShowLoading[id_name + parameters]);
Element.remove(this.inShowLoading[id_name + parameters]);
this.inShowLoading[id_name + parameters] = null;
return true;
}
return false;
},
showModal: function(event, el, loading_flag) {
el = (event == undefined || event == null) ? el : this;
var scroll_left = (document.documentElement.scrollLeft || document.body.scrollLeft || 0);
var scroll_top = (document.documentElement.scrollTop || document.body.scrollTop || 0);
var offset = 0;
var w = Position.getWinOuterWidth();
var h = Position.getWinOuterHeight();
el.style.width =  (w + scroll_left - offset)  +"px";
el.style.height =  (h + scroll_top - offset) +"px";
if(loading_flag) {
el.style.backgroundColor = "#cccccc";
Element.setStyle(el, {"opacity":0.2});
}
el.style.position = "absolute";
el.style.left = "0px";
el.style.top = "0px";
if(event == undefined || (event.type != "scroll" && event.type != "resize")) {
commonCls.max_zIndex = commonCls.max_zIndex + 1;
el.style.zIndex = commonCls.max_zIndex;
commonCls.inModalEvent[el] = commonCls.showModal.bindAsEventListener(el);
Event.observe(window,"scroll",commonCls.inModalEvent[el],false);
Event.observe(window,"resize",commonCls.inModalEvent[el],false);
if(browser.isIE) {
var img_blank = document.createElement("img");
img_blank.src = _nc_core_base_url + "/images/common/blank.gif";
el.appendChild(img_blank);
}
if(browser.isIE) {
if(img_blank==undefined) {
var img_blank = Element.getChildElement(el);
}
img_blank.style.width = el.style.width;
img_blank.style.height = el.style.height;
}
}
},
stopModal: function(el) {
Event.stopObserving(window,"scroll", commonCls.inModalEvent[el], false);
Event.stopObserving(window,"resize",commonCls.inModalEvent[el],false);
commonCls.inModalEvent[el] = null;
},
sendView: function(id, parameter, params, headermenu_flag) {
var top_el = $(id);
if(params == undefined) {
var params = new Object();
}
params["focus_flag"] = 1;
if(typeof parameter == 'string') {
var re_action = new RegExp("^action=", 'i');
if(parameter.match(re_action)) {
params["param"] = parameter;
} else {
params["param"] = {"action":parameter};
}
} else {
params["param"] = parameter;
}
params["top_el"] = top_el;
var content = "";
if(headermenu_flag != null && headermenu_flag != undefined) {
var headermenu = Element.getChildElementByClassName(top_el,"_headermenu");
if(headermenu) {
var div_headermenu = document.createElement("DIV");
div_headermenu.className = headermenu.className;
div_headermenu.innerHTML = headermenu.innerHTML;
params["headermenu"] = div_headermenu;
}
}
if(params["target_el"] === undefined) params["target_el"] = top_el.parentNode;
if(params["loading_el"] === undefined) params["loading_el"] = top_el;
commonCls.send(params);
},
sendPost: function(id, parameter, post_params) {
var top_el = null;
if(id) {
top_el = $(id);
}
if(post_params == undefined) {
var post_params = new Object();
}
if(typeof parameter == 'string') {
var re_action = new RegExp("^action=", 'i');
if(parameter.match(re_action)) {
post_params["param"] = parameter;
} else {
post_params["param"] = {"action":parameter};
}
} else {
post_params["param"] = parameter;
}
if(!post_params["method"]) post_params["method"] = "post";
post_params["top_el"] = top_el;
if(post_params["loading_el"] === undefined) post_params["loading_el"] = top_el;
commonCls.send(post_params);
},
sendRefresh: function(id, params) {
commonCls.sendView(id, commonCls.getUrl($(id)).parseQuery(), params);
},
sendPopupView: function(event, parameter, params) {
if(params == undefined) {
var params = new Object();
}
if(parameter != undefined && parameter != null) {
params["param"] = parameter;
} else {
parameter = params["param"];
}
if(params['top_el'] != undefined || params['top_el'] != null) {
params['url'] = commonCls._paramEncode(params['param'], params['form_el']);
params = commonCls._setParam(params);
params['top_el_id'] = params['top_el'].id;
var id = commonCls._getId(params['url']);
} else if(parameter && parameter.tagName == undefined && typeof parameter == 'object') {
var param_str = "";
for(key in parameter) {
if(param_str != "") {
param_str += "&";
}
param_str += key + "=" + parameter[key];
}
var id = commonCls._getId(param_str);
} else {
var id = commonCls._getId(parameter);
}
if(!commonCls.moduleList[id] || params['modal_flag'] == true) {
commonCls.moduleList[id] = "dummy";
if(!params["loading_el"] && event) {
params["loading_el"] = Event.element(event);
}
params["create_flag"] = true;
if(event) {
params["event"] = event;
}
params["callbackfunc_error"] = function(res) {commonCls.alert(res);commonCls.moduleList[id]=null;}.bind(id);
commonCls.send(params);
} else if(commonCls.moduleList[id] == "dummy") {
} else {
var current_el = commonCls.moduleList[id];
var top_el = params["top_el"]
if(params['center_flag']) {
var center_position = commonCls.getCenterPosition(current_el, top_el);
var x = center_position[0];
var y = center_position[1];
} else {
var x = (params['x'] != null && params['x'] != undefined) ? params['x'] : Event.pointerX(event);
var y = (params['y'] != null && params['y'] != undefined) ? params['y'] : Event.pointerY(event);
}
current_el.style.left = x + "px";
current_el.style.top = y + "px";
var move_pos = commonCls.moveAutoPosition(current_el);
if(move_pos != null) {
x = move_pos[0];
y = move_pos[1];
}
if(commonCls.moduleList[id].style.zIndex != commonCls.max_zIndex) {
commonCls.max_zIndex = commonCls.max_zIndex + 1;
current_el.style.zIndex = commonCls.max_zIndex;
}
}
},
closeCallbackFunc: function(id, func) {
this.closeCallbackFuncEvent[id] = func;
},
displayBlockChange: function(id) {
var block_el = $(id);
var content = Element.getChildElementByClassName(block_el,"content");
commonCls.displayChange(content);
commonCls.moveVisibleHide(block_el);
},
removeBlock: function(id) {
var block_el = $(id);
if (typeof id != 'string') {
id = id.id;
}
var block_el = $(id);
if(block_el.parentNode && block_el.parentNode.tagName.toLowerCase() == "body") {
return true;
}
Event.unloadCache(id);
if(id) {
var _global_modal_dialog = $("_global_modal_dialog" + id);
}
if(!_global_modal_dialog) {
_global_modal_dialog = $("_global_modal_dialog");
}
if(_global_modal_dialog) {
Element.remove(_global_modal_dialog);
commonCls.stopModal(_global_modal_dialog);
}
var get_id = commonCls._getId(block_el);
delete commonCls.moduleList[get_id];
commonCls.moduleList[get_id] = null;
commonCls.displayChange(block_el);
commonCls.moveVisibleHide(block_el);
var parent_el = block_el.parentNode;
Element.remove(block_el);
if(parent_el && Element.hasClassName(parent_el,"_global_create_block")) {
Element.remove(parent_el);
}
if(this.closeCallbackFuncEvent[id]) {
this.closeCallbackFuncEvent[id]();
delete this.closeCallbackFuncEvent[id];
this.closeCallbackFuncEvent[id] = null;
}
},
moveVisibleHide: function(el) {
el = (el && el.nodeType==1) ? el : this;
var offset = Position.cumulativeOffset(el);
var ex1 = offset[0];
var ex2 = el.offsetWidth + ex1;
var ey1 = offset[1];
var ey2 = el.offsetHeight + ey1;
var id_name = (el.id == "" || Element.hasClassName(el,"_global_create_block")) ? el.childNodes[0].id : el.id;
if(commonCls.hideElement[id_name] == null)
commonCls.hideElement[id_name] = Array();
if(browser.isIE && browser.version < 7) {
var tags = new Array("applet", "select", "object","embed");
} else {
var tags = new Array("embed", "object");
}
var tags_length = tags.length;
for (var k = tags_length; k > 0; ) {
var target_ar = document.getElementsByTagName(tags[--k]);
var target_ar_length = target_ar.length;
for (var i = target_ar_length; i > 0;) {
var target = target_ar[--i];
offset = Position.cumulativeOffset(target);
var cx1 = offset[0];
var cx2 = target.offsetWidth + cx1;
var cy1 = offset[1];
var cy2 = target.offsetHeight + cy1;
if (((cx1 > ex2) || (cx2 < ex1) || (cy1 > ey2) || (cy2 < ey1))) {
if(Element.hasClassName(target,"visible-hide")) {
for (var key = 0,hide_el_length = commonCls.hideElement[id_name].length; key < hide_el_length; key++) {
var value = commonCls.hideElement[id_name][key];
if(value) {
if(target == value) {
commonCls.hideElement[id_name][key] = null;
Element.removeClassName(target,"visible-hide");
break;
}
}
}
}
} else {
var children = el.getElementsByTagName('*') || document.all;
var chk_flag = true;
var children_length = children.length;
for (var j = 0; j < children_length; j++) {
var child = children[j];
if(child == target) {
chk_flag = false;
break;
}
}
if(chk_flag) {
if(!Element.hasClassName(target,"visible-hide")) {
commonCls.hideElement[id_name][commonCls.hideElement[id_name].length] = target;
Element.addClassName(target,"visible-hide");
}
} else {
if(Element.hasClassName(target,"visible-hide")) {
Element.removeClassName(target,"visible-hide");
for (var key = 0,hide_el_length = commonCls.hideElement[id_name].length; key < hide_el_length; key++) {
var value = commonCls.hideElement[id_name][key];
if(value) {
if(target == value)
commonCls.hideElement[id_name][key] = null;
}
}
}
}
}
}
}
},
winMoveDragStart: function(event) {
var page_id_name =this.id;
var id = commonCls._getId(this);
if(commonCls.inMoveDrag[id]) {
return false;
}
var this_el = commonCls.moduleList[id];
if(this_el.style.zIndex != commonCls.max_zIndex) {
commonCls.max_zIndex = commonCls.max_zIndex + 1;
this_el.style.zIndex = commonCls.max_zIndex;
commonCls.moveVisibleHide(this_el);
}
commonCls.move_div[id] = this_el;
commonCls.start_x[id] = Event.pointerX(event);
commonCls.start_y[id] = Event.pointerY(event);
commonCls.show_x[id] = valueParseInt(commonCls.moduleList[id].style.left);
commonCls.show_y[id] = valueParseInt(commonCls.moduleList[id].style.top);
commonCls.pre_show_x[id] = valueParseInt(commonCls.moduleList[id].style.left);
commonCls.pre_show_y[id] = valueParseInt(commonCls.moduleList[id].style.top);
commonCls.winMoveDragGoEvent[id] = commonCls.winMoveDragGo.bindAsEventListener(this);
commonCls.winMoveDragStopEvent[id] = commonCls.winMoveDragStop.bindAsEventListener(this);
Event.observe(document,"mousemove",commonCls.winMoveDragGoEvent[id],true);
Event.observe(document,"mouseup",commonCls.winMoveDragStopEvent[id],true);
Event.stop(event);
commonCls.inMoveDrag[id] = true;
},
winMoveDragGo: function(event) {
var page_id_name =this.id;
var id = commonCls._getId(this);
if(!commonCls.inMoveDrag[id]) {
return false;
}
var x = Event.pointerX(event);
var y = Event.pointerY(event);
var def_px = 5;
if(x <= commonCls.start_x[id] + def_px && x >= commonCls.start_x[id] - def_px &&
y <= commonCls.start_y[id] + def_px && y >= commonCls.start_y[id] - def_px) {
return false;
}
var show_x = commonCls.show_x[id] - (commonCls.start_x[id] - x);
var show_y = commonCls.show_y[id] - (commonCls.start_y[id] - y);
if(show_x < 0)
show_x = 0;
if(show_y < 0)
show_y = 0;
commonCls.pre_show_x[id] = valueParseInt(commonCls.moduleList[id].style.left);
commonCls.pre_show_y[id] = valueParseInt(commonCls.moduleList[id].style.top);
commonCls.moduleList[id].style.left = show_x +"px";
commonCls.moduleList[id].style.top = show_y +"px";
commonCls.moveVisibleHide(commonCls.moduleList[id]);
Event.stop(event);
},
winMoveDragStop: function(event) {
var page_id_name =this.id;
var id = commonCls._getId(this);
if(!commonCls.inMoveDrag[id]) {
commonCls.inMoveDrag[id] = false;
return false;
}
Event.stopObserving(document,"mousemove",commonCls.winMoveDragGoEvent[id],true);
Event.stopObserving(document,"mouseup",commonCls.winMoveDragStopEvent[id],true);
commonCls.winMoveDragGoEvent[id] = null;
commonCls.winMoveDragStopEvent[id] = null;
Event.stop(event);
commonCls.show_x[id] = valueParseInt(commonCls.moduleList[id].style.left);
commonCls.show_y[id] = valueParseInt(commonCls.moduleList[id].style.top);
var interval = 50;
commonCls.speedx[id] = (commonCls.show_x[id]-commonCls.pre_show_x[id]);
commonCls.speedy[id] = (commonCls.show_y[id]-commonCls.pre_show_y[id]);
if(commonCls.speedx[id] > 10)
commonCls.speedx[id] = 10;
else if(commonCls.speedx[id] < -10)
commonCls.speedx[id] = -10;
if(commonCls.speedy[id] > 10)
commonCls.speedy[id] = 10;
else if(commonCls.speedy[id] < -10)
commonCls.speedy[id] = -10;
setTimeout("commonCls.winMoveDragStopAfter(\""+ id +"\")", interval);
commonCls.inMoveDrag[id] = false;
},
winMoveDragStopAfter: function(id) {
if(commonCls.speedx[id] > 0)
commonCls.speedx[id] = commonCls.speedx[id] - 1;
else if(commonCls.speedx[id] < 0)
commonCls.speedx[id] = commonCls.speedx[id] + 1;
if(commonCls.speedy[id] > 0)
commonCls.speedy[id] = commonCls.speedy[id] - 1;
else if(commonCls.speedy[id] < 0)
commonCls.speedy[id] = commonCls.speedy[id] + 1;
var show_x = valueParseInt(commonCls.move_div[id].style.left) + commonCls.speedx[id];
var show_y = valueParseInt(commonCls.move_div[id].style.top) + commonCls.speedy[id];
if(show_x < 0)
show_x = 0;
if(show_y < 0)
show_y = 0;
commonCls.move_div[id].style.left = show_x +"px";
commonCls.move_div[id].style.top = show_y +"px";
commonCls.show_x[id] = show_x;
commonCls.show_y[id] = show_y;
if(commonCls.speedx[id] != 0 || commonCls.speedy[id] != 0) {
var interval = 50;
setTimeout("commonCls.winMoveDragStopAfter(\""+ id +"\")", interval);
}
},
blockNotice: function(event, el) {
if(typeof(Event.element) != 'undefined') {
var el = (el == undefined) ? Event.element(event) : el;
if(!Element.hasClassName(el,"highlight")) {
var rgbBack = commonCls.getRGBtoHex(Element.getStyle(el, "backgroundColor"));
if (rgbBack == "transparent") {
var parent_el = el;
while (rgbBack == "transparent") {
if(parent_el.tagName == "BODY") {
rgbBack = new Object();
rgbBack.r = 255;
rgbBack.g = 255;
rgbBack.b = 255;
break;
}
var parent_el = parent_el.parentNode;
rgbBack = commonCls.getRGBtoHex(Element.getStyle(parent_el, "backgroundColor"));
}
}
Element.addClassName(el,"highlight");
setTimeout(function(){
commonCls.blockNoticeTimer(el, rgbBack);
}, 200);
}
}
},
blockNoticeTimer: function(el,rgbBack) {
var offset = 10;
var rgb = commonCls.getRGBtoHex(Element.getStyle(el, "backgroundColor"));
if (rgb == "transparent") {
var parent_el = el;
while (rgb == "transparent") {
if(parent_el.tagName == "BODY") {
rgb = new Object();
rgb.r = 255;
rgb.g = 255;
rgb.b = 255;
break;
}
var parent_el = parent_el.parentNode;
rgb = commonCls.getRGBtoHex(Element.getStyle(parent_el, "backgroundColor"));
}
}
if(rgb.r > rgbBack.r) rgb.r = (rgb.r - offset < rgbBack.r) ? rgbBack.r : rgb.r - offset;
else if(rgb.r < rgbBack.r) rgb.r = (rgb.r + offset > rgbBack.r) ? rgbBack.r : rgb.r + offset;
if(rgb.g > rgbBack.g) rgb.g = (rgb.g - offset < rgbBack.g) ? rgbBack.g : rgb.g - offset;
else if(rgb.g < rgbBack.g) rgb.g = (rgb.g + offset > rgbBack.g) ? rgbBack.g : rgb.g + offset;
if(rgb.b > rgbBack.b) rgb.b = (rgb.b - offset < rgbBack.b) ? rgbBack.b : rgb.b - offset;
else if(rgb.b < rgbBack.b) rgb.b = (rgb.b + offset > rgbBack.b) ? rgbBack.b : rgb.b + offset;
Element.setStyle(el, {"backgroundColor":commonCls.getHex(rgb.r,rgb.g,rgb.b)});
if(rgb.r == rgbBack.r && rgb.g == rgbBack.g && rgb.b == rgbBack.b) {
if(Element.hasClassName(el,"highlight"))Element.removeClassName(el,"highlight");
Element.setStyle(el, {"backgroundColor":""});
} else {
setTimeout(function(){
commonCls.blockNoticeTimer(el, rgbBack);
}, 200);
}
},
getParams: function(top_el) {
var url = commonCls.getUrl(top_el);
if(url) {
var re_cut = new RegExp(".*\\?", "i");
url = url.replace(re_cut,"").replace(/&amp;/g,"&");
var queryParams = url.parseQuery();
return queryParams;
} else
return false;
},
getUrl: function(top_el) {
if (typeof top_el == 'string') {
top_el = $(top_el);
} else if(top_el.tagName == "DIV" && Element.hasClassName(top_el,"cell")) {
top_el = Element.getChildElement(top_el);
}
var url_el = $("_url"+ top_el.id);
if(!url_el){url_el = Element.getChildElementByClassName(top_el,"_url");}
if(url_el) {
return url_el.value.replace(/&amp;/g,"&");
} else
return false;
},
getBlockid: function(top_el) {
if(top_el.tagName == "DIV" && Element.hasClassName(top_el,"cell")) {
top_el = Element.getChildElement(top_el);
}
var id_name = top_el.id;
if(!id_name) {
return false;
}
return id_name.substr(1, id_name.length);
},
setToken: function(id, token_value) {
var token_el = $(id);
if(token_el) token_el.value = token_value;
},
getToken: function(top_el) {
if (typeof top_el == 'string') {
top_el = $(top_el);
} else if(top_el.tagName == "DIV" && Element.hasClassName(top_el,"cell")) {
top_el = Element.getChildElement(top_el);
}
var token_el = $("_token"+ top_el.id);
if(!token_el){token_el = Element.getChildElementByClassName(top_el,"_token");}
if(token_el) {
return token_el.value;
} else
return false;
},
send: function(params_obj) {
if(params_obj['url'] == null || params_obj['url'] == undefined) {
params_obj['url'] = commonCls._paramEncode(params_obj['param'], params_obj['form_el']);
}
if(params_obj['url'] == "") {
var error_mes = "The parameter is illegal.";
if(callbackfunc_error){
if(params_obj['func_error_param'] == undefined) {params_obj['callbackfunc_error'](error_mes);}
else{params_obj['callbackfunc_error'](params_obj['func_error_param'],error_mes);}
} else {
_debugShow(error_mes);
}
return false;
}
params_obj['method'] = (params_obj['method'] == undefined || params_obj['method'] == null) ? "get" : params_obj['method'];
params_obj['token'] = (params_obj['token'] == undefined || params_obj['token'] == null) ? "" : params_obj['token'];
params_obj['header_flag'] = (params_obj['header_flag'] == undefined || params_obj['header_flag'] == null) ? false : params_obj['header_flag'];
params_obj['create_flag'] = (params_obj['create_flag'] == undefined || params_obj['create_flag'] == null) ? false : params_obj['create_flag'];
params_obj['center_flag'] = (params_obj['center_flag'] == undefined || params_obj['center_flag'] == null) ? false : params_obj['center_flag'];
if(params_obj['create_flag'] && !params_obj['center_flag']) {
params_obj['x'] = (params_obj['x'] == undefined || params_obj['x'] == null) ? Event.pointerX(params_obj['event']) : params_obj['x'];
params_obj['y'] = (params_obj['y'] == undefined || params_obj['y'] == null) ? Event.pointerY(params_obj['event']) : params_obj['y'];
}
params_obj['show_main_flag'] = false;
if(!params_obj['create_flag'] && params_obj['center_flag']) {
params_obj['center_col'] = $("_centercolumn");
if(params_obj['center_col']) {
params_obj['show_main_flag'] = true;
}
}
params_obj['eval_flag'] = (params_obj['eval_flag'] == undefined || params_obj['eval_flag'] == null) ? 1 : parseInt(params_obj['eval_flag']);
if(params_obj['top_el_id'] == undefined && (params_obj['top_el'] != undefined && params_obj['top_el'] != null)) {
params_obj = commonCls._setParam(params_obj);
} else {
params_obj['top_el_id'] = "";
}
if(params_obj['token']) {params_obj['url'] = params_obj['url'] + "&_token=" + params_obj['token'];}
if(params_obj['header_flag']) {params_obj['url'] += "&_header=1";}else{ params_obj['url'] += "&_header=0";}
if(params_obj['show_main_flag']) {params_obj['url'] += "&_show_main_flag=1";}
if(params_obj['debug']) {commonCls._debugShow(params_obj['url']);}
params_obj['complete_flag'] = false;
new Ajax.Request(_nc_base_url + _nc_index_file_name , {
method:     params_obj['method'],
noautoeval: true,
parameters: params_obj['url'],
requestHeaders: ["Referer",_nc_current_url],
onLoading: function() {
if(!this['complete_flag'] && (this['loading_el'] || (this['loading_x'] && this['loading_y']))) {
commonCls.showLoading(this['top_el_id'],this['url'],this['loading_x'],this['loading_y'],this['loading_el']);
}
}.bind(params_obj),
onComplete: function(transport) {
_nc_global_script_write_html = '';
_nc_global_script_span = null;
this['complete_flag'] = true;
if(this['debug']) {commonCls._debugShow(transport.responseText);}
if(this['loading_el'] || (this['loading_x'] && this['loading_y'])){commonCls.hideLoading(this['top_el_id'],this['url']);}
if(this['target_el'] && this['top_el_id'] != "" && this['target_el'].id == this['top_el_id']) {
this['target_el'] = this['target_el'].parentNode;
}
var target_flag = false;
if(this['create_flag']) {
this['target_el'] = document.createElement("DIV");
Element.addClassName(this['target_el'],"_global_create_block");
target_flag = true;
}
if(_nc_debug) var res = commonCls.AjaxResultStr(transport.responseText);
else var res = transport.responseText;
if((this['match_str'] != null && this['match_str'] != undefined && commonCls.matchContentElement(res,this['match_str'])) ||
((this['match_str'] == null || this['match_str'] == undefined) && !commonCls.matchErrorElement(res))) {
res = commonCls.cutErrorMes(res);
if(this['target_el'] || this['show_main_flag']) {
if(browser.isGecko && this['target_el']) {
var hidden_el = this['target_el'];
hidden_el.style.visibility = "hidden";
}
if(!this['create_flag'] && this['center_flag']) {
if(this['center_col']) {
this['center_col'].innerHTML = "<div class='enlarged_display'>"+res+"</div>";
}
}
if(!this['show_main_flag']) {
var div_write = document.createElement('div');
div_write.innerHTML = res;
if(target_flag) {
if(this['modal_flag']) {
var div_parent = document.createElement("DIV");
if(this['target_el']) {
var child_target_el = Element.getChildElement(div_write);
if(child_target_el.id) {
div_parent.id = "_global_modal_dialog" + child_target_el.id;
}
}
if(!div_parent.id) {
div_parent.id = "_global_modal_dialog";
}
commonCls.showModal(null, div_parent, true);
document.body.appendChild(div_parent);
}
document.body.appendChild(this['target_el']);
}
_nc_dwScriptCount = 0;
_nc_dwScriptList = Array();
var scriptList = div_write.getElementsByTagName("script");
var addScriptList = Array();
var addParentScriptList = Array();
var count = 0;
for (var i = 0,scriptLen = scriptList.length; i < scriptLen; i++){
if(!Element.hasClassName(scriptList[i], "nc_script")) {
_nc_dwScriptList[count] = scriptList[i];
if((browser.isIE || browser.isSafari)) {
if((scriptList[i].src == undefined || scriptList[i].src == "")) {
addScriptList[count] = scriptList[i];
} else {
var script_el = document.createElement('script');
script_el.setAttribute('type', 'text/javascript');
script_el.setAttribute('src', scriptList[i].src);
addScriptList[count] = script_el;
}
} else {
var script_el = document.createElement('script');
script_el.id = "_nc_script"+ count;
script_el.type = "text/javascript";
script_el.innerHTML = "_nc_dwScriptCount = " + count + "; Element.remove($(\"_nc_script"+ count +"\"));";
addScriptList[count] = script_el;
}
addParentScriptList[count] = scriptList[count];
count++;
}
}
if(div_el == undefined) var div_el = null;
_nc_ajaxFlag = true;
this['target_el'].innerHTML = "";
if(this['target_el'] && Element.hasClassName(this['target_el'],"module_box")) {
Event.unloadCache(this['target_el']);
}
for (var i = 0, n = div_write.childNodes.length; i < n ; ++i) {
this['target_el'].appendChild(div_write.childNodes[i]);
n--;
i--;
}
for (var i = 0,scriptLen = addScriptList.length; i < scriptLen; i++){
if((browser.isIE || browser.isSafari) && (addScriptList[i].src == undefined || addScriptList[i].src == "") && addScriptList[i].innerHTML != "") {
eval(addScriptList[i].innerHTML);
} else {
addParentScriptList[i].parentNode.insertBefore(addScriptList[i], addParentScriptList[i]);
}
}
setTimeout(function(){_nc_ajaxFlag = false;}, 1000);
if(target_flag) {
this['target_el'] = Element.getChildElement(this['target_el']);
}
if(this['headermenu']) {
var content_el = Element.getChildElementByClassName(this['target_el'],"content");
content_el.parentNode.insertBefore(this['headermenu'], content_el);
}
}
}
if(this['top_el_id'] && this['focus_flag']){
var a_el = $("_href"+this['top_el_id']);
if(a_el) a_el.focus();
}
if(this['eval_flag'] && (!this['target_el'] || browser.isIE || browser.isSafari || browser.isOpera || (browser.isFirefox && browser.version >= 4))) {
commonCls.AjaxResultScript(transport.responseText);
}
if(this['create_flag'] && this['center_flag']) {
var center_position = commonCls.getCenterPosition(this['target_el'], this['top_el']);
this['x'] = center_position[0];
this['y'] = center_position[1];
}
if(this['create_flag']) {
var id_name = this['target_el'].id;
var id = commonCls._getId(this['target_el']);
if(id) {
commonCls.show_x[id] = this['x'];
commonCls.show_y[id] = this['y'];
}
this['target_el'].parentNode.style.left = commonCls.show_x[id] +"px";
this['target_el'].parentNode.style.top = commonCls.show_y[id] +"px";
var move_pos = commonCls.moveAutoPosition(this['target_el'].parentNode);
}
if(browser.isGecko && hidden_el) {
hidden_el.style.visibility = "visible";
}
if(this['callbackfunc']){
if (transport.getResponseHeader("Content-Type").substring(0, 8) === "text/xml" && transport.responseXML) {
res = transport.responseXML;
}
if(this['func_param'] == undefined) {this['callbackfunc'](res);}
else {this['callbackfunc'](this['func_param'],res);}
}
eval("document.write('');");
return true;
} else {
res = commonCls.cutErrorMes(res);
if(res !== "") {
var re_html = new RegExp("^<!DOCTYPE html", 'i');
if(!res.match(re_html)) {
var re_script = new RegExp('<script.*?>((.|\n|\r|\t)*?)<\/script>', 'ig');
res = res.replace(re_script,"");
}
if(this['callbackfunc_error']){
if(this['func_error_param'] == undefined) {this['callbackfunc_error'](res);}
else{this['callbackfunc_error'](this['func_error_param'],res);}
} else {
commonCls.alert(res);
}
if(this['eval_flag']) {
commonCls.AjaxResultScript(transport.responseText);
}
}
return false;
}
}.bind(params_obj)
});
commonCls.setTimeoutAlert();
},
_setParam: function(params_obj) {
if (typeof params_obj['top_el'] == 'string') {
params_obj['top_el_id'] = params_obj['top_el'];
params_obj['top_el'] = $(params_obj['top_el']);
} else {
params_obj['top_el_id'] = params_obj['top_el'].id;
}
if(params_obj['token'] == "") {
var token_el = $("_token"+ params_obj['top_el_id']);
if(!token_el){token_el = Element.getChildElementByClassName(params_obj['top_el'],"_token");}
if(token_el){params_obj['token'] = token_el.value;}
}
var queryParams = commonCls.getParams(params_obj['top_el']);
if(queryParams) {
var page_id = queryParams["page_id"];
var block_id = (queryParams["block_id"] == undefined) ? 0 : queryParams["block_id"];
var module_id = queryParams["module_id"];
var params_id = "";
var queryParams = params_obj['url'].parseQuery();
if(page_id && !queryParams['page_id'])params_id += "&page_id=" + page_id;
if(block_id && !queryParams['block_id'])params_id += "&block_id=" + block_id;
if(module_id && !queryParams['module_id'])params_id += "&module_id=" + module_id;
if(!queryParams['prefix_id_name']) {
if(block_id != 0) var suffix_id = block_id;
else var suffix_id = module_id;
if(suffix_id != undefined && suffix_id.length + 1 != params_obj['top_el_id'].length) {
var re_suffix_id = new RegExp("_"+suffix_id + "$", "i");
var replace_str = params_obj['top_el_id'].replace(re_suffix_id,"");
if(replace_str == params_obj['top_el_id']) {
var re_suffix_id = new RegExp("_"+block_id + "$", "i");
var replace_str = params_obj['top_el_id'].replace(re_suffix_id,"");
}
replace_str = replace_str.substr(1,replace_str.length - 1);
if(replace_str != "") {
params_id += "&prefix_id_name=" + replace_str;
}
}
}
params_obj['url'] = params_obj['url'] + params_id;
}
return params_obj;
},
moveAutoPosition: function(target_el, move_pos_str) {
move_pos_str = (move_pos_str == undefined) ? "both" : move_pos_str;
var move_pos = new Array();
var buf_left = valueParseInt(target_el.style.left);
var buf_top = valueParseInt(target_el.style.top);
if(!browser.isGecko || buf_left <= 0) move_pos[0] = target_el.offsetLeft;
else move_pos[0] = buf_left;
if(!browser.isGecko || buf_top <= 0) move_pos[1] = target_el.offsetTop;
else move_pos[1] = buf_top;
var move_pos_flag = false;
var popupX1 = move_pos[1] + target_el.offsetHeight;
var bodyX1 = Position.getWinOuterHeight() + (window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop);
if(Element.hasClassName(target_el,"_global_create_block")) {
var popupX2 = move_pos[0] + Element.getChildElement(target_el).offsetWidth;
} else {
var popupX2 = move_pos[0] + target_el.offsetWidth;
}
var bodyX2 = Position.getWinOuterWidth() + (window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft);
if ((move_pos_str == "both" || move_pos_str == "y") && popupX1 > bodyX1) {
var buf_y =  move_pos[1] - (popupX1 - bodyX1);
move_pos[1] =  (buf_y > 0) ? buf_y : (move_pos[1] > 0 ? move_pos[1] : 0);
target_el.style.top = move_pos[1] +"px";
move_pos_flag = true;
}
if ((move_pos_str == "both" || move_pos_str == "x") && popupX2 > bodyX2) {
move_pos[0] =  move_pos[0] - (popupX2 - bodyX2);
if (move_pos[0] < 0) {
move_pos[0] = 0;
}
target_el.style.left = move_pos[0] +"px";
move_pos_flag = true;
}
if(move_pos_flag) {
return move_pos;
}
return null;
},
_debugShow: function(error_mes) {
if(typeof debug == 'object') {debug.p(error_mes);} else {commonCls.alert(error_mes);}
},
getCenterPosition: function(target_el, position_el) {
Position.prepare();
var offset_target = new Object();
offset_target[0] = 0;
offset_target[1] = 0;
if(position_el == undefined) {
var w = Position.getWinOuterWidth() + Position.deltaX;
var h = Position.getWinOuterHeight() + Position.deltaY;
} else {
position_el = $(position_el);
var w = position_el.offsetWidth;
var h = position_el.offsetHeight;
offset_target = Position.cumulativeOffset(position_el);
}
var position = new Object();
position[0] = ((w - target_el.offsetWidth) / 2) + offset_target[0];
position[1] = ((h - target_el.offsetHeight) / 2) + offset_target[1];
if(position[0] < 0) {
position[0] = 0;
}
if(position[1] < 0) {
position[1] = 0;
}
return position;
},
_paramEncode: function(parameter, form_el) {
var return_param = "";
if(parameter != undefined || parameter != null) {
if (typeof parameter == 'object') {
var queryComponents = new Array();
for(var key in parameter) {
if (typeof parameter[key] == 'object' || typeof parameter[key] == 'array') {
queryComponents = createParam(parameter[key], encodeURIComponent(key),queryComponents);
} else {
var queryComponent = encodeURIComponent(key) + '=' + encodeURIComponent(parameter[key]);
if (queryComponent) {
queryComponents.push(queryComponent);
}
}
}
return_param = queryComponents.join('&');
} else if(typeof parameter == 'string') {
parameter = parameter.unescapeHTML();
var re_base_url = new RegExp("^" + _nc_base_url + _nc_index_file_name +"\\?", "i");
return_param = parameter.replace(re_base_url,"");
if (!commonCls.matchContentElement(return_param,"action=")) {
return_param = "action=" + return_param;
}
}
}
if(form_el && form_el.tagName == "form") {
return_param = (return_param == "") ? Form.serialize(form_el) : return_param + "&" + Form.serialize(form_el);
}
function createParam(parameter, key_str,queryComponents) {
var ret_array = queryComponents;
for(var key in parameter) {
var key_sub_str= key_str + "["+key+"]";
if (typeof parameter[key] == 'object' || typeof parameter == 'array') {
ret_array.push(createParam(parameter[key], key_sub_str,queryComponents));
} else {
var str = typeof parameter[key];
if (str == 'string') {
ret_array.push(key_sub_str + "=" + encodeURIComponent(parameter[key]));
}
}
}
return ret_array;
}
return return_param;
},
AjaxResultStr: function(res) {
var re_log = new RegExp("<div class=\"logger_block\">(.|\n|\r|\t)*<\/div>", 'i');
var logger_block = res.match(re_log);
if(logger_block) {
var count = 0;
for(var i = 0; i < logger_block.length; i++) {
if(logger_block[i].trim() != "") {
if(count == 0) {
var winlogger = window.open("", "",
"height=200,width=400,menubar=yes,scrollbars=yes,resizable=yes");
winlogger.document.open("text/html", "replace");
winlogger.document.write("<HTML><HEAD><link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\""+_nc_core_base_url + _nc_index_file_name + "?action=common_download_css&amp;dir_name=/css/common.css&amp;header=0"+"\" /></HEAD><BODY>");
winlogger.document.write(logger_block[i].trim());
} else {
winlogger.document.write(logger_block[i].trim());
}
count++;
}
}
if(count != 0) {
var re_fatal = new RegExp(commonCls.fatal_error_mes, 'i');
var fatal_mes = res.match(re_fatal);
if(fatal_mes) winlogger.document.write(fatal_mes[0].trim());
winlogger.document.write("</BODY></HTML>");
winlogger.document.close();
winlogger.focus();
}
res = res.replace(re_log,"").trim();
} else {
var re_fatal = new RegExp(commonCls.fatal_error_mes, 'i');
if(res.match(re_fatal)) {
var winlogger = window.open("", "",
"height=200,width=400,menubar=yes,scrollbars=yes,resizable=yes");
winlogger.document.open("text/html", "replace");
winlogger.document.write("<HTML><HEAD><link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\""+_nc_core_base_url + _nc_index_file_name + "?action=common_download_css&amp;dir_name=/css/common.css&header=0"+"\" /></HEAD><BODY>"+res+"</BODY></HTML>");
winlogger.document.close();
winlogger.focus();
res = commonCls.error_mes + res;
}
}
return res;
},
AjaxResultScript: function(res) {
var re_script = new RegExp('<script class=\"nc_script\"[^>]*?>((.|\n|\r|\t)*?)<\/script>', 'ig');
var re_common_global_array = res.match(re_script);
var re_common_global_array_eval = "";
if(re_common_global_array) {
for(var common_global_counter = 0; common_global_counter < re_common_global_array.length; common_global_counter++) {
var re_common_global_array_eval = re_common_global_array[common_global_counter].replace(re_script,"$1");
if(re_common_global_array_eval.trim() == "") {
} else {
eval(re_common_global_array_eval);
}
}
}
},
parentWinInit: function(el,absolute_flag, chief_flag) {
var re_underbar = new RegExp('^_', 'i');
var el = (el.id == null || el.id == "") ? Element.getChildElement(el) : el;
var module_id_name = el.id;
if(el && module_id_name && module_id_name.match(re_underbar)) {
var id = commonCls._getId(el);
if(absolute_flag) {
var parent_el = el.parentNode;
if(parent_el) {
parent_el.style.position = "absolute";
if(parent_el.style.zIndex != commonCls.max_zIndex) {
commonCls.max_zIndex = commonCls.max_zIndex + 1;
parent_el.style.zIndex = commonCls.max_zIndex;
}
setTimeout(commonCls.moveVisibleHide.bind(el), 0);
commonCls.moduleList[id] = parent_el;
}
}
if(absolute_flag) {
var move_bar = Element.getChildElementByClassName(el,"_move_bar");
commonCls.winMoveDragStartEvent[id] = commonCls.winMoveDragStart.bindAsEventListener(el);
Event.observe(move_bar,"mousedown",commonCls.winMoveDragStartEvent[id],false, el);
} else {
if(chief_flag) {
var _block_title = Element.getChildElementByClassName(el,"nc_block_title");
var _block_title_event = Element.getChildElementByClassName(el,"_block_title_event");
if(_block_title && Element.hasClassName(el,"module_grouping_box")) {
var buf_module_box_el = Element.getParentElementByClassName(_block_title,"module_box");
if(buf_module_box_el.id != el.id) _block_title = null;
}
if(_block_title_event && Element.hasClassName(el,"module_grouping_box")) {
var buf_module_box_el = Element.getParentElementByClassName(_block_title_event,"module_box");
if(buf_module_box_el.id != el.id) _block_title_event = null;
}
if(!_block_title_event) _block_title_event = _block_title;
if(_block_title_event) {
Event.observe(_block_title, "mouseover", commonCls.blockNotice, false, el);
Event.observe(_block_title_event,"dblclick",pagesCls.blockChangeName.bindAsEventListener(_block_title),false, el);
}
var theme_header_flag = true;
var theme_top_el = $("_theme_top" + el.id);
if(theme_top_el) {
var move_bar = Element.getChildElementByClassName(theme_top_el,"_move_bar");
if(move_bar) {
theme_header_flag = false;
pagesCls.winMoveDragStartEvent[el.id] = pagesCls.winMoveDragStart.bindAsEventListener(el);
Event.observe(move_bar,"mousedown",pagesCls.winMoveDragStartEvent[el.id],false, el);
pagesCls.winGroupingEvent[el.id] = pagesCls.onGroupingEvent.bindAsEventListener(el);
Event.observe(move_bar,"click",pagesCls.winGroupingEvent[el.id],false, el);
}
}
if(theme_header_flag && _nc_layoutmode == "on") {
setTimeout(function() {
pagesCls.winMoveResizeHeader();
}.bind(this), 200);
Event.observe(el,"mouseover",pagesCls.winMoveShowHeader.bindAsEventListener(el),false, el);
Event.observe(el,"mouseout",pagesCls.winMoveHideHeader.bindAsEventListener(el),false, el);
}
}
}
}
},
matchErrorElement: function(str) {
var match_table = new RegExp("^(?:" + this.error_mes + "|<!DOCTYPE html){1}.+", "i");
if (typeof str == 'string' && str.match(match_table)) {
return true;
}
return false;
},
matchContentElement: function(str,match_str) {
if (match_str == "") {
if(str == "") {
return true;
} else {
return false;
}
} else {
var match_div = new RegExp(match_str, "i");
if (str.match(match_div)) {
return true;
} else {
return false;
}
}
},
cutParamByUrl: function(url) {
var re_cut = new RegExp(".*\\?", "i");
url = "?" + url.replace(re_cut,"");
return url;
},
_getId: function(top_el,name) {
var key = (name == undefined || name == null) ? "" : "?" + name ;
if (typeof top_el == 'string'){
var queryParams = commonCls.cutParamByUrl(top_el).parseQuery();
} else {
var queryParams = commonCls.getParams(top_el);
}
if(queryParams["block_id"] != null && queryParams["block_id"] != 0) {
key += "&block_id=" + queryParams["block_id"];
}
if(queryParams["page_id"] != null && queryParams["page_id"] != 0) {
key += "&page_id=" + queryParams["page_id"];
}
if((queryParams["block_id"] == null || queryParams["block_id"] == 0) && queryParams["action"] != "") {
key += "&dir_name=" + queryParams["action"].split("_")[0]
}
var prefix_id_name = queryParams["prefix_id_name"];
if(prefix_id_name != null) {
key += "&prefix_id_name=" + prefix_id_name;
}
return key;
},
alert: function(str) {
if(typeof str != 'string') return "";
var re_html = new RegExp("^<!DOCTYPE html", 'i');
if(str.match(re_html)) {
document._write(str);
} else {
str = commonCls.cutErrorMes(str);
str = str.unescapeHTML();
str = str.replace(/\\n/ig,"\n");
str = str.replace(/(<br(?:.|\s|\/)*?>)/ig,"\n");
if(str != "") {
alert(str);
}
}
},
confirm: function(str) {
if(typeof str != 'string') return "";
var re_html = new RegExp("^<!DOCTYPE html", 'i');
if(str.match(re_html)) {
document.write(str);
} else {
str = str.unescapeHTML();
str = str.replace(/\\n/ig,"\n");
str = str.replace(/(<br(?:.|\s|\/)*?>)/ig,"\n");
return confirm(str);
}
},
cutErrorMes: function(str) {
if(typeof str != 'string') return "";
var re_error = new RegExp("^" + this.error_mes, 'i');
if(str.match(re_error)) {
str = str.substr(this.error_mes.length,str.length);
}
return str;
},
displayChange: function(el) {
el = $(el);
var elestyle = el.style;
if (elestyle.display == "none" || Element.hasClassName(el,"display-none")) {
this.displayVisible(el);
} else {
this.displayNone(el);
}
},
displayNone: function(el) {
var elestyle = el.style;
if (elestyle.display) {
elestyle.display = "none";
}
Element.addClassName(el,"display-none");
},
displayVisible: function(el) {
var elestyle = el.style;
var display = "";
if (el.tagName == "TR") display = "";
else if (el.tagName == "TD") display = "";
else if (el.tagName == "TABLE") display = "";
elestyle.display = display;
if(Element.hasClassName(el,"display-none")) {
Element.removeClassName(el,"display-none");
}
try {
if (!(browser.isIE || browser.isOpera || browser.isSafari)) {
var iframeList = el.getElementsByTagName("iframe");
for (var i = 0; i < iframeList.length; i++){
if(iframeList[i].contentWindow.document.designMode == "on") {
iframeList[i].contentWindow.document.designMode = "off";
iframeList[i].contentWindow.document.designMode = "on";
}
}
}
}catch(e){}
},
visibilityChange: function(el) {
el = $(el);
if(Element.hasClassName(el,"visible-hide")) {
commonCls.visibilityVisible(el);
} else {
commonCls.visibilityNone(el);
}
},
visibilityNone: function(el) {
Element.addClassName(el,"visible-hide");
},
visibilityVisible: function(el) {
if(Element.hasClassName(el,"visible-hide")) {
Element.removeClassName(el,"visible-hide");
} else {
el.style.visibility = "visible";
}
},
cellIndex: function(element) {
if(browser.isSafari) {
for (var i = 0; i < element.parentNode.childNodes.length; i++) {
if(element.parentNode.childNodes[i] == element) {
return i;
}
}
} else {
return element.cellIndex;
}
return 0;
},
showPopupImageFullScale: function(this_el) {
if($("_fullscall_image")) {
return;
}
var img_el = Element.getChildElement(this_el);
var div_el = document.createElement("DIV");
Element.setStyle(div_el, {opacity:0.7});
div_el.id = "_global_full_scale";
div_el.style.backgroundColor = "#666666";
document.body.appendChild(div_el);
commonCls.showModal(null, div_el);
var new_img_el = document.createElement("IMG");
commonCls.max_zIndex = commonCls.max_zIndex + 1;
new_img_el.style.zIndex = commonCls.max_zIndex;
new_img_el.style.position = "absolute";
new_img_el.src = img_el.src;
new_img_el.style.visibility = "hidden";
new_img_el.id = "_fullscall_image";
document.body.appendChild(new_img_el);
var center_position = commonCls.getCenterPosition(new_img_el, img_el);
new_img_el.style.left = center_position[0] + "px";
new_img_el.style.top = center_position[1] + "px";
new_img_el.style.visibility = "visible";
commonCls.moveVisibleHide(div_el);
div_el.onmousedown = function() {
commonCls.displayChange(div_el);
commonCls.moveVisibleHide(div_el);
Element.remove(div_el);
Element.remove(new_img_el);
}
new_img_el.onmousedown = function() {
commonCls.displayChange(div_el);
commonCls.moveVisibleHide(div_el);
Element.remove(div_el);
Element.remove(new_img_el);
}
},
addCommonLink: function (dir_name, media, document_object){
document_object = (document_object == undefined || document_object == null) ? document : document_object;
var nLink = null;
var new_dir_name_arr = new Array();
var del_dir_name_arr = new Array();
var common_css_flag = false;
for(var i=0; (nLink = document_object.getElementsByTagName("LINK")[i]); i++) {
if(Element.hasClassName(nLink, "_common_css")) {
common_css_flag = true;
var queryParams = nLink.href.unescapeHTML().parseQuery();
if(!queryParams["dir_name"])
continue;
var dir_name_arr = queryParams["dir_name"].split("|");
var current_dir_name_arr = dir_name.split("|");
for (var j = 0; j < current_dir_name_arr.length; j++){
var pos = dir_name_arr.indexOf(current_dir_name_arr[j]);
if(pos == -1) {
var new_pos = new_dir_name_arr.indexOf(current_dir_name_arr[j]);
if(new_pos != -1) break;
new_dir_name_arr[new_dir_name_arr.length] = current_dir_name_arr[j];
} else {
del_dir_name_arr[del_dir_name_arr.length] = current_dir_name_arr[j];
}
}
}
}
del_dir_name_arr.each(function(del_value) {
new_dir_name_arr = new_dir_name_arr.without(del_value);
}.bind(this));
var new_dir_name = new_dir_name_arr.join("|");
if(new_dir_name == "") {
if(common_css_flag) {
return true;
} else {
new_dir_name = dir_name
}
}
var css_name = _nc_core_base_url + _nc_index_file_name + "?action=common_download_css&amp;dir_name="+new_dir_name+"&amp;header=0&amp;vs="+_nc_css_vs;
return commonCls._addLink(css_name, media, document_object, "_common_css");
},
addLink: function (css_name, media, document_object){
document_object = (document_object == undefined || document_object == null) ? document : document_object;
var nLink = null;
for(var i=0; (nLink = document_object.getElementsByTagName("LINK")[i]); i++) {
if(nLink.href == css_name) {
return true;
}
}
return commonCls._addLink(css_name, media, document_object);
},
_addLink: function (css_name, media, document_object, class_name){
if(typeof document_object.createStyleSheet != 'undefined') {
document_object.createStyleSheet(css_name.unescapeHTML());
var oLinks = document_object.getElementsByTagName('LINK');
var nLink = oLinks[oLinks.length-1];
} else if(document_object.styleSheets){
var nLink=document_object.createElement('LINK');
nLink.rel="stylesheet";
nLink.type="text/css";
nLink.media= (media ? media : "screen");
nLink.href=css_name.unescapeHTML();
var oHEAD=document_object.getElementsByTagName('HEAD').item(0);
oHEAD.appendChild(nLink);
}
if(class_name != undefined) {
Element.addClassName(nLink, class_name);
}
return true;
},
scriptDocWrite: function (src_name, document_object){
document_object = (document_object == undefined || document_object == null) ? document : document_object;
var nScript = null;
for(var i=0; (nScript = document_object.getElementsByTagName("SCRIPT")[i]); i++) {
if(nScript.src != "" && nScript.src == src_name) {
return;
}
}
document_object.open();
document_object.write('<script type="text/javascript" src= "' + src_name + '"></script>');
document_object.close();
},
addScript: function (src_name, document_object){
document_object = (document_object == undefined || document_object == null) ? document : document_object;
var nScript = null;
for(var i=0; (nScript = document_object.getElementsByTagName("SCRIPT")[i]); i++) {
if(nScript.src != "" && nScript.src == src_name) {
return true;
}
}
var nScript=document_object.createElement('SCRIPT');
nScript.type="text/javascript";
nScript.src=src_name;
var oHEAD=document_object.getElementsByTagName('HEAD').item(0);
oHEAD.appendChild(nScript);
return true;
},
frmTransValue: function (frm, efrom, eto){
var ef = frm.elements[efrom];
var et = frm.elements[eto];
while (ef.selectedIndex != -1) {
if(!ef.disabled) {
et.length = et.length + 1;
et.options[et.length - 1].value = ef.options[ef.selectedIndex].value;
et.options[et.length - 1].text = ef.options[ef.selectedIndex].text;
ef.options[ef.selectedIndex] = null;
}
}
},
frmMoveListBox: function(frm, e, move) {
var selectindx = frm.elements[e].selectedIndex;
if (selectindx != -1){
if (move == 1) {
for( i = 0; i < frm.elements[e].length; i++ ){
if( frm.elements[e].options[i].selected ){
if( i <= 0 ) {
continue;
}
var optText = frm.elements[e].options[i].text;
var optValue = frm.elements[e].options[i].value;
frm.elements[e].options[i].text = frm.elements[e].options[i-1].text;
frm.elements[e].options[i].value = frm.elements[e].options[i-1].value;
frm.elements[e].options[i-1].text = optText;
frm.elements[e].options[i-1].value = optValue;
frm.elements[e].options[i-1].selected=true;
frm.elements[e].options[i].selected=false;
}
}
} else if (move > 1) {
var j=0;
for( i = 0; i < frm.elements[e].length; i++ ){
if( frm.elements[e].options[i].selected ){
if( i <= 0 ) {
continue;
}
var optText = frm.elements[e].options[i].text;
var optValue = frm.elements[e].options[i].value;
var eleOption = document.createElement("option");
eleOption.value = optValue;
eleOption.text = optText;
frm.elements[e].options[i] = null;
commonCls.frmAddOption(frm.elements[e], eleOption, j);
frm.elements[e].options[j].selected=true;
j++;
}
}
} else if (move == -1) {
for( i = frm.elements[e].length-1; i >= 0; i-- ){
if( frm.elements[e].options[i].selected ){
if( i >= frm.elements[e].length-1 ) {
continue;
}
var optText = frm.elements[e].options[i].text;
var optValue = frm.elements[e].options[i].value;
frm.elements[e].options[i].text = frm.elements[e].options[i+1].text;
frm.elements[e].options[i].value = frm.elements[e].options[i+1].value;
frm.elements[e].options[i+1].text = optText;
frm.elements[e].options[i+1].value = optValue;
frm.elements[e].options[i+1].selected=true;
frm.elements[e].options[i].selected=false;
}
}
} else if (move < -1) {
var j=frm.elements[e].length - 1;
for( i = frm.elements[e].length-1; i >= 0; i-- ){
if( frm.elements[e].options[i].selected ){
if( i >= frm.elements[e].length-1 ) {
continue;
}
var optText = frm.elements[e].options[i].text;
var optValue = frm.elements[e].options[i].value;
var eleOption = document.createElement("option");
eleOption.value = optValue;
eleOption.text = optText;
frm.elements[e].options[i] = null;
commonCls.frmAddOption(frm.elements[e], eleOption, j);
frm.elements[e].options[j].selected=true;
j--;
}
}
}
}
},
frmAddOption: function(eleSelect, eleOption, index) {
if (browser.isNS){
eleSelect.insertBefore(eleOption, eleSelect.options[index]);
}else{
eleSelect.options.add( eleOption, index );
}
},
frmAllReleaseList: function(frm, e) {
frm.elements[e].selectedIndex = -1;
},
frmAllSelectList: function(frm, e, disabled_flag) {
if ( frm.elements[e] == undefined ) {
}else{
var n = frm.elements[e].length;
for (var i = 0; i < n ; i++) {
if((disabled_flag == undefined || disabled_flag == false) || !frm.elements[e].options[i].disabled) {
frm.elements[e].options[i].selected = true;
}
}
}
},
frmAllSelectRadio: function(frm, value, callback_checked_func) {
for ( i=0; i < frm.elements.length; i++ ){
if ( frm.elements[i].type == 'radio' ){
if(frm.elements[i].value == value && !frm.elements[i].disabled) {
if(callback_checked_func == undefined) {
frm.elements[i].checked = true;
} else {
callback_checked_func(frm.elements[i]);
}
}
}
}
},
frmAllChecked: function(frm, e, value) {
if ( frm.elements[e] == undefined ) {
frm.elements[e].checked = value;
}else{
var n = frm.elements[e].length;
if ( n == undefined ) {
frm.elements[e].checked = value;
} else {
for (var i = 0; i < n ; i++) {
frm.elements[e][i].checked = value;
}
}
}
},
setLineBreak: function(str, number_char)	{
if(number_char == undefined) number_char = 30;
var reg_exp_obj = new RegExp("((?:.|\s){" + number_char + "})", "g");
return str.replace(reg_exp_obj, "$1<br />");
},
print: function(el, width, height, header_flag, window_name)	{
width = (width == undefined) ? 600 : width;
height = (height == undefined) ? 600 : height;
header_flag = (header_flag == undefined) ? true : header_flag;
window_name = (window_name == undefined) ? commonLang.printTitle : window_name;
if(header_flag) {
var html = "<div class=\"print_header\"><a class=\"print_btn link\" href=\"javascript:window.close();\">"+commonLang.close+"</a>"+
commonLang.separator+"<a class=\"print_btn link\" href=\"javascript:window.print();\">"+commonLang.print+"</a></div>";
} else {
var html = "";
}
var print_script = "";
var disabled_script = "window.opener.commonCls.disableLink(document.body, \"print_btn\", true);";
var re_script = new RegExp('<script.*?>((.|\n|\r|\t)*?)<\/script>', 'ig');
if(typeof el == 'string') {
html += "<div class=\"outerdiv\">";
html += el.replace(re_script,"");
} else {
if(!el.id) {
var print_id = "_global_print_el";
el.id = print_id;
} else {
var print_id = el.id;
}
html += "<div id=\""+ el.id +"\" class=\"outerdiv"+ el.className +"\">";
if(!browser.isGecko) {
html += el.innerHTML.replace(re_script,"");
} else {
var append_el = el.cloneNode(true);
print_script = "document.getElementById('"+print_id+"').appendChild(print_el);"+disabled_script;
}
}
html += "</div>";
var features="location=no, menubar=no, status=yes, scrollbars=yes, resizable=yes, toolbar=no";
if (width) {
if (window.screen.width > width)
features+=", left="+(window.screen.width-width)/2;
else width=window.screen.width;
features += ", width="+width;
}
if (height) {
if (window.screen.height > height)
features+=", top="+(window.screen.height-height)/2;
else height=window.screen.height;
features+=", height="+height;
}
var head = document.getElementsByTagName("head")[0];
var links = head.getElementsByTagName("link");
var linkText = "<link rel=\"stylesheet\" type=\"text/css\" media=\"print\" href=\""+_nc_core_base_url + _nc_index_file_name + "?action=common_download_css&amp;dir_name=/css/print.css&amp;header=0"+"\" />";
linkText += "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\""+_nc_core_base_url + _nc_index_file_name + "?action=common_download_css&amp;dir_name=/css/print_preview.css&amp;header=0"+"\" />";
for (var i = 0; i < links.length; i++) {
var link = links[i];
if (link.getAttribute("type") == "text/css") {
linkText += "<link ";
linkText += "rel=\"" + link.getAttribute("rel") + "\" ";
linkText += "type=\"" + link.getAttribute("type") + "\" ";
linkText += "media=\"" + link.getAttribute("media") + "\" ";
linkText += "href=\"" + link.getAttribute("href") + "\" ";
linkText += "/>\n";
}
}
var scriptText = '';
if(print_script == "") {
var scriptTextPrint =  "<script>function Init() {setTimeout(function(){"+print_script+disabled_script+" print();}, 500);}</script>";
} else {
var scriptTextPrint =  "<script>function Init() {setTimeout(function(){"+print_script+" print();}, 500);}</script>";
}
var scriptList = document.getElementsByTagName("script");
for (var i = 0,scriptLen = scriptList.length; i < scriptLen; i++){
if((scriptList[i].src != undefined && scriptList[i].src != "")) {
scriptText += "<script type=\"text/javascript\" src=\""+scriptList[i].src+"\"></script>";
}
}
var winprint = window.open("", "PrintPreview", features);
if(append_el != undefined) {
winprint.print_el = append_el;
}
winprint.document.open("text/html");
winprint.document.write("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"><html><head><title>" + window_name + "</title><style> html,body {background-image : none !important; padding:0px !important; margin:0px !important;}</style>" + linkText + scriptText + "</head>"+"<body class=\"print_preview\" onload=\"Init();\">"+html+scriptTextPrint+"</body></html>");
winprint.document.close();
},
disableLink: function(el, enable_class, parent_flag)	{
el = $(el);
var hasClassName = (parent_flag == undefined) ? function(el, class_name) {return commonCls.hasClassName(el, class_name);}.bind(this) : function(el, class_name) {return window.parent.commonCls.hasClassName(el, class_name);}.bind(this);
var aList = el.getElementsByTagName("A");
for (var i = 0,aLen = aList.length; i < aLen; i++) {
if(enable_class != undefined && !hasClassName(aList[i], enable_class)) {
aList[i].onclick = function(){return false;};
}
}
var inputList = el.getElementsByTagName("INPUT");
for (var i = 0,inputLen = inputList.length; i < inputLen; i++) {
if(enable_class != undefined && !hasClassName(inputList[i], enable_class) &&
inputList[i].type.toLowerCase() == "button") {
inputList[i].onclick = function(){return false;};
}
}
},
observeTooltip: function(observe_el, top_id, show_mes, show_second)	{
if (top_id == 'string') {
var top_el = $(top_id);
} else {
var top_el = top_id;
top_id = top_id.id;
}
commonCls.inToolTipEvent["mouseover"+top_id] = function(event) {
commonCls.showTooltip(event, show_mes, show_second);
}.bindAsEventListener(this);
commonCls.inToolTipEvent["mouseout"+top_id] = function(event) {
commonCls.closeTooltip(event);
}.bindAsEventListener(this);
Event.observe(observe_el, "mouseover", commonCls.inToolTipEvent["mouseover"+top_id],false, top_el);
Event.observe(observe_el, "mouseout",  commonCls.inToolTipEvent["mouseout"+top_id], false, top_el);
},
showTooltip: function(event, show_mes, show_second)	{
if(this.toolTipPopup == null) {
this.toolTipPopup   = new compPopup(null, "popupTooltip");
this.toolTipPopup.observing = false;
this.toolTipPopup.modal = false;
this.toolTipPopup.setTitle('Tooltip');
this.toolTipPopup.loadObserver = function() {
var popupY2 = this.popupElement.offsetTop + this.popupElement.offsetHeight;
var bodyY2 = Position.getWinOuterHeight() + document.documentElement.scrollTop;
if (popupY2 > bodyY2) {
var new_position = new Array();
new_position[0] = this.popupElement.offsetLeft;
if (new_position[0] < 0) {
new_position[0] = 0;
}
new_position[1] = this.popupElement.offsetTop - (popupY2 - bodyY2);
this.setPosition(new_position);
}
}.bind(this.toolTipPopup);
}
if(this.toolTipPopup.isVisible()) return;
var observe_el = Event.element(event);
if(show_second == undefined) show_second = 5000;
var div = document.createElement("DIV");
Element.addClassName(div, "tooltipClass");
div.innerHTML = show_mes;
var offset = 20;
var position = new Object;
position[0] = Event.pointerX(event);
position[1] = Event.pointerY(event) + offset;
this.toolTipPopup.setPosition(position);
this.toolTipPopup.showPopup(div);
if(this.toolTipPopupTimer != null) {
clearTimeout(this.toolTipPopupTimer);
this.toolTipPopupTimer = null;
}
this.toolTipPopupTimer = setTimeout(function(){this.closeTooltip(event)}.bind(this), show_second);
},
closeTooltip: function(event)	{
if(this.toolTipPopup == null || !this.toolTipPopup.isVisible()) return;
this.toolTipPopup.closePopup();
},
showUserDetail: function(event, user_id) {
if(_nc_user_id == "0") return;
user_id = (user_id == undefined) ? 0 : user_id;
var param_popup = new Object();
var user_params = new Object();
param_popup = {
"action":"userinf_view_main_init",
"prefix_id_name":"popup_userinf"+user_id,
"user_id":user_id,
"theme_name": "system"
};
user_params['callbackfunc_error'] = function(res){};
commonCls.sendPopupView(event, param_popup, user_params);
},
sendAttachment: function(params_obj) {
if (typeof params_obj['top_el'] == 'string') {
var id = params_obj['top_el'];
var top_el = $(params_obj['top_el']);
} else {
var id = params_obj['top_el'].id;
var top_el = params_obj['top_el'];
}
var match_str = params_obj['match_str'];
var form_prefix = (params_obj['form_prefix'] != undefined && params_obj['form_prefix'] != null) ? params_obj['form_prefix'] : "attachment_form";
var form_target = form_prefix + id;
var download_action = (params_obj['download_action'] != undefined && params_obj['download_action'] != null) ? params_obj['download_action'] : "common_download_main";
var header_flag = (params_obj['header_flag'] != undefined && params_obj['header_flag'] != null) ? params_obj['header_flag'] : 0;
var callbackfunc = (params_obj['callbackfunc'] != undefined && params_obj['callbackfunc'] != null) ? params_obj['callbackfunc'] : null;
var callbackfunc_error = (params_obj['callbackfunc_error'] != undefined && params_obj['callbackfunc_error'] != null) ? params_obj['callbackfunc_error'] : null;
var target_el = (params_obj['target_el'] != undefined && params_obj['target_el'] != null) ? params_obj['target_el'] : null;
var debug_param = (params_obj['debug'] != undefined && params_obj['debug'] != null) ? params_obj['debug'] : 0;
var timeout_flag = (params_obj['timeout_flag'] != undefined && params_obj['timeout_flag'] != null) ? params_obj['timeout_flag'] : 1;
if(debug_param) {debug_param = 1;}
if(commonCls.inAttachment[form_target] != null) {
return;
}
commonCls.inAttachment[form_target] = true;
if (params_obj['document_obj']) {
var document_object = params_obj['document_obj'];
var formList = document_object.getElementsByTagName("form");
} else {
var document_object = document;
var formList = top_el.getElementsByTagName("form");
}
for (var i = 0; i < formList.length; i++){
if(formList[i].target == form_target) {
if(params_obj['param'] != undefined || params_obj['param'] != null) {
var action_flag = false;
var name_arr = new Array();
var value_arr = new Object();
var count = 0;
for(var key in params_obj['param']){
if(key == "action") {
action_flag = true;
}
name_arr[count] = key;
value_arr[key] = encodeURIComponent(params_obj['param'][key]);
count++;
}
if(!action_flag) {
return false;
}
var token_el = Element.getChildElementByClassName(top_el, "_token");
var queryParams = commonCls.getParams(top_el);
var block_id = (queryParams["block_id"] == undefined) ? 0 : queryParams["block_id"];
var page_id = queryParams["page_id"];
var module_id = queryParams["module_id"];
name_arr[count++] = "download_action_name";
value_arr['download_action_name'] = download_action;
name_arr[count++] = "_attachment_callback";
value_arr['_attachment_callback'] = "tmp_" + form_target;
name_arr[count++] = "_header";
value_arr['_header'] = header_flag;
if(token_el) {
name_arr[count++] = "_token"
value_arr['_token'] = token_el.value;
}
name_arr[count++] = "block_id";
value_arr['block_id'] = block_id;
name_arr[count++] = "page_id";
value_arr['page_id'] = page_id;
name_arr[count++] = "module_id";
value_arr['module_id'] = module_id;
if(!queryParams['prefix_id_name']) {
if(block_id != 0) var att_suffix_id = block_id;
else var att_suffix_id = module_id;
if(att_suffix_id.length + 1 != id.length) {
var att_re_suffix_id = new RegExp("_"+att_suffix_id + "$", "i");
var att_replace_str = id.replace(att_re_suffix_id,"");
if(att_replace_str == id) {
var att_re_suffix_id = new RegExp("_"+block_id + "$", "i");
var att_replace_str = id.replace(att_re_suffix_id,"");
}
att_replace_str = att_replace_str.substr(1,att_replace_str.length - 1);
if(att_replace_str != "") {
name_arr[count++] = "prefix_id_name";
value_arr['prefix_id_name'] = att_replace_str;
}
}
} else {
name_arr[count++] = "prefix_id_name";
value_arr['prefix_id_name'] = queryParams["prefix_id_name"];
}
name_arr = _checkInputTag(name_arr, formList[i]);
this.attachmentCallBack[form_target] = callbackfunc;
this.attachmentErrorCallBack[form_target] = callbackfunc_error;
this.attachmentTarget[form_target] = target_el;
var div=document_object.createElement('div');
div.id = "tmp_" + form_target;
div.style.visibility = "hidden";
div.innerHTML='<iframe src="about:blank" name="' + form_target + '" style="width:0px;height:0px;"></iframe>';
document_object.body.appendChild(div);
for (var j = 0; j < name_arr.length; j++){
if(value_arr[name_arr[j]] || value_arr[name_arr[j]] == 0) {
if(name_arr[j] != "action") {
_createHiddenTag(name_arr[j],value_arr[name_arr[j]],formList[i]);
} else {
var action_name = value_arr[name_arr[j]];
}
}
}
formList[i].action = _nc_base_url + _nc_index_file_name + '?action=' + action_name;
if(action_name != undefined) {
_createHiddenTag("action", action_name, formList[i]);
}
formList[i].method = "post";
commonCls.referObject = document_object;
formList[i].submit();
commonCls._attachmentChecker(form_target, match_str, debug_param, 0, timeout_flag);
var attachment_hiddenfields = Element.getElementsByClassName(formList[i], "_attachment_hidden");
attachment_hiddenfields.each(function(el) {
Element.remove(el);
}.bind(this));
return true;
}
}
}
return false;
function _checkInputTag(name_arr, form_el){
var inputList = form_el.getElementsByTagName("input");
for (var j = 0; j < inputList.length; j++){
if(inputList[j].name) {
var pos = name_arr.indexOf(inputList[j].name);
if(pos >= 0) {
name_arr[pos] = null;
}
}
}
return name_arr.compact();
}
function _createHiddenTag(key_name, value, form_el){
var input=document_object.createElement('input');
input.setAttribute("name",key_name,1);
input.setAttribute("type","hidden",1);
input.value = value;
Element.addClassName(input, "_attachment_hidden");
form_el.appendChild(input);
}
},
_attachmentChecker: function(form_target, match_str, debug_param, totaltime, timeout_flag) {
var iframe_target_el = commonCls.referObject.getElementById("tmp_" + form_target);
if(browser.isSafari) {
if(Element.getChildElement(iframe_target_el).contentWindow) {
if(Element.getChildElement(iframe_target_el).contentWindow.document && Element.getChildElement(iframe_target_el).contentWindow.document.body) {
var div = Element.getChildElementByClassName(Element.getChildElement(iframe_target_el).contentWindow.document.body, "_attachment_result");
if(div) {
Element.addClassName(iframe_target_el, "_attachment_end")
}
}
}
}
if(totaltime > 30000 && timeout_flag == 1) {
if (!commonCls.confirm(commonLang.upload_timeout_confirm)) {
Element.remove(iframe_target_el);
commonCls.inAttachment[form_target] = null;
return;
}
totaltime = 0;
}
if(!Element.hasClassName(iframe_target_el, "_attachment_end")) {
if(match_str == null || match_str == undefined) {
setTimeout("commonCls._attachmentChecker('"+form_target+"',"+match_str+","+debug_param+","+(totaltime+200)+","+timeout_flag+")", 200);
} else {
setTimeout("commonCls._attachmentChecker('"+form_target+"','"+match_str+"',"+debug_param+","+(totaltime+200)+","+timeout_flag+")", 200);
}
} else {
iframe_target_el.innerHTML = Element.getChildElement(iframe_target_el).contentWindow.document.body.innerHTML;
var callback_func = commonCls.attachmentCallBack[form_target];
var callbackfunc_error = commonCls.attachmentErrorCallBack[form_target];
var target_el = commonCls.attachmentTarget[form_target];
commonCls.attachmentCallBack[form_target] = null;
commonCls.attachmentErrorCallBack[form_target] = null;
commonCls.attachmentTarget[form_target] = null;
commonCls.inAttachment[form_target] = null;
var div = Element.getChildElementByClassName(iframe_target_el, "_attachment_result");
if(div) {
var response = new Object();
for (var i = 0; i < div.childNodes.length; i++) {
var file = div.childNodes[i];
response[i] = new Object();
for (var j = 0; j < file.childNodes.length; j++) {
response[i][file.childNodes[j].title] = file.childNodes[j].innerHTML;
}
}
Element.remove(div);
}
var res = iframe_target_el.innerHTML;
if(_nc_debug) var res = commonCls.AjaxResultStr(res);
if(debug_param) {
if(typeof debug == 'object') {
debug.p(res);
} else {
commonCls.alert(res);
}
}
commonCls.referObject = null;
if((match_str != null && match_str != undefined && commonCls.matchContentElement(res,match_str)) ||
((match_str == null || match_str == undefined) && !commonCls.matchErrorElement(res))) {
if(target_el) {
target_el.innerHTML = res;
}
if(callback_func) {
callback_func(response, res);
}
} else {
res = commonCls.cutErrorMes(res);
if(callbackfunc_error) {
callbackfunc_error(response, res);
} else {
commonCls.alert(res);
}
}
Element.remove(iframe_target_el);
}
},
imgChange: function(el, prev_name, change_name, alt_title_str) {
var img_el = (el.tagName.toLowerCase() == "img") ? el : el.getElementsByTagName("img")[0];
if(img_el) {
prev_name=prev_name.replace(/(\!|"|'|\(|\)|\-|\=|\^|\\|\||\[|\{|\+|\:|\*|\]|\}|\,|\<|\.|\>|\/|\?)/g,"\\$1");
var re = new RegExp(prev_name + "$", "i");
img_el.src = img_el.src.replace(re, change_name);
if(alt_title_str != undefined) {
img_el.title = alt_title_str;
img_el.alt = alt_title_str;
}
}
},
tabsetActive: function(this_el) {
var targetEl = this_el;
if(!Element.hasClassName(targetEl,"comptabset_tabset")) {
var targetEl = Element.getParentElementByClassName(targetEl,"comptabset_tabset");
}
var tab_el = Element.getParentElementByClassName(targetEl,"comptabset_tabs");
var tableList = tab_el.getElementsByTagName("table");
var active_flag = true;
for (var i = 0; i < tableList.length; i++){
if(Element.hasClassName(tableList[i],"comptabset_tabset")) {
if(targetEl == tableList[i] || targetEl.parentNode == tableList[i]) {
if(Element.hasClassName(tableList[i],"comptabset_active")) {
active_flag = false;
break;
} else {
Element.addClassName(tableList[i],"comptabset_active");
}
} else {
Element.removeClassName(tableList[i],"comptabset_active");
}
}
}
return active_flag;
},
tabsetFocus: function(id) {
var headermenu_el = $("_headermenu"+ id);
var active_el = Element.getChildElementByClassName(headermenu_el,"comptabset_active");
var a_el = active_el.getElementsByTagName("a")[0];
commonCls.focus(a_el);
},
focus: function(id) {
if (typeof id == 'string') {
setTimeout("commonCls.focusComp('"+id+"')", 300);
} else {
setTimeout(function(){commonCls.focusComp(this);}.bind(id), 300);
}
},
focusComp: function(id, error_count) {
try {
error_count = (error_count == undefined) ? 0 : error_count;
if (typeof id == 'string') {
var top_el = $(id);
var form = top_el.getElementsByTagName("form")[0];
if(form) {
var result = Form.focusFirstElement(form);
}
} else {
var result = false;
if(id.nodeType == 1) {
var top_el = id;
var name =id.tagName.toLowerCase();
if(name == 'input' || name == 'select' || name == 'textarea') {
id.focus();
id.select();
result = true;
} else if(name == 'a') {
id.focus();
result = true;
} else if(name == 'form') {
result = Form.focusFirstElement(id);
} else {
var inputList = id.getElementsByTagName("input");
for (var i = 0; i < inputList.length; i++){
if ((inputList[i].type == "text" || inputList[i].type == "select" || inputList[i].type == "textarea")
&& !inputList[i].disabled){
inputList[i].focus();
inputList[i].select();
result = true;
break;
}
}
}
}
}
if(!result && top_el) {
var a_el = top_el.getElementsByTagName("a")[0];
if(a_el) a_el.focus();
}
}catch(e){
if(error_count < 5) {
error_count++;
if (typeof id == 'string') {
setTimeout("commonCls.focusComp('"+id+"'," + error_count + ")", 300);
} else {
setTimeout(function(){commonCls.focusComp(this, error_count);}.bind(id), 300);
}
}
}
},
addBlockTheme: function(theme_name) {
var themeStrList = theme_name.split("_");
if(themeStrList.length == 1) {
var template_block_dir = "themes/" + theme_name + "/css/";
} else {
theme_name = themeStrList.shift();
var template_block_dir = "themes/" + theme_name + "/css/" + themeStrList.join("/") + "/";
}
commonCls.addCommonLink("/" + template_block_dir+"style.css");
},
scrollMoveDrag: function(event, offset) {
var offset = (offset == undefined) ? 40 : offset;
Position.prepare();
if(Event.pointerX(event) - Position.deltaX  > Position.getWinOuterWidth() - offset) {
scrollTo(Position.deltaX + offset -10, Position.deltaY);
}else if(Event.pointerX(event)  <= Position.deltaX + offset && Position.deltaX > 0) {
scrollTo(Position.deltaX - offset, Position.deltaY);
}
if(Event.pointerY(event) - Position.deltaY  > Position.getWinOuterHeight() - offset) {
scrollTo(Position.deltaX, Position.deltaY + offset);
} else if(Event.pointerY(event)  <= Position.deltaY + offset && Position.deltaY > 0) {
scrollTo(Position.deltaX, Position.deltaY - offset);
}
},
getHSL : function(r, g, b)
{
var h,s,l,v,m;
var r = r/255;
var g = g/255;
var b = b/255;
v = Math.max(r, g), v = Math.max(v, b);
m = Math.min(r, g), m = Math.min(m, b);
l = (m+v)/2;
if (v == m) var sl_s = 0, sl_l = Math.round(l*255),sl_h=0;
else
{
if (l <= 0.5) s = (v-m)/(v+m);
else s = (v-m)/(2-v-m);
if (r == v) h = (g-b)/(v-m);
if (g == v) h = 2+(b-r)/(v-m);
if (b == v) h = 4+(r-g)/(v-m);
h = h*60; if (h<0) h += 360;
var sl_h = Math.round(h/360*255);
var sl_s = Math.round(s*255);
var sl_l = Math.round(l*255);
}
return { h : sl_h, s : sl_s , l : sl_l };
},
getRBG : function(h, s, l)
{
var r, g, b, v, m, se, mid1, mid2;
h = h/255, s = s/255, l = l/255;
if (l <= 0.5) v = l*(1+s);
else v = l+s-l*s;
if (v <= 0) var sl_r = 0, sl_g = 0, sl_b = 0;
else
{
var m = 2*l-v,h=h*6, se = Math.floor(h);
var mid1 = m+v*(v-m)/v*(h-se);
var mid2 = v-v*(v-m)/v*(h-se);
switch (se)
{
case 0 : r = v;    g = mid1; b = m;    break;
case 1 : r = mid2; g = v;    b = m;    break;
case 2 : r = m;    g = v;    b = mid1; break;
case 3 : r = m;    g = mid2; b = v;    break;
case 4 : r = mid1; g = m;    b = v;    break;
case 5 : r = v;    g = m;    b = mid2; break;
}
var sl_r = Math.round(r*255);
var sl_g = Math.round(g*255);
var sl_b = Math.round(b*255);
}
return { r : sl_r, g : sl_g , b : sl_b };
},
getRGBtoHex : function(color) {
if(color.r ) return color;
if(color == "transparent" || color.match("^rgba")) return "transparent";
if(color.match("^rgb")) {
color = color.replace("rgb(","");
color = color.replace(")","");
color_arr = color.split(",");
return { r : parseInt(color_arr[0]), g : parseInt(color_arr[1]) , b : parseInt(color_arr[2]) };
}
if ( color.indexOf('#') == 0 )
color = color.substring(1);
var red   = color.substring(0,2);
var green = color.substring(2,4);
var blue  = color.substring(4,6);
return { r : parseInt(red,16), g : parseInt(green,16) , b : parseInt(blue,16) };
},
getHex : function(r, g, b)
{
var co = "#";
if (r < 16) co = co+"0"; co = co+r.toString(16);
if (g < 16) co = co+"0"; co = co+g.toString(16);
if (b < 16) co = co+"0"; co = co+b.toString(16);
return co;
},
getColorCode: function(el , property_name) {
if(property_name == "borderColor" || property_name == "border-color") {
property_name = "borderTopColor";
}
if(property_name == "borderTopColor" || property_name == "borderRightColor" ||
property_name == "borderBottomColor" || property_name == "borderLeftColor") {
var width = Element.getStyle(el, property_name.replace("Color","")+"Width");
if(width == "" || width == "0px" || width == "0") {
return "transparent";
}
}
var rgb = Element.getStyle(el, property_name);
if(rgb == undefined || rgb == null) {
return "transparent";
} else if (rgb.match("^rgba") && rgb != "transparent" && rgb.substr(0, 1) != "#") {
rgb = rgb.substr(5, rgb.length - 6);
var rgbArr = rgb.split(",");
if(rgbArr[3].trim() == "0")
rgb = "";
else
rgb = commonCls.getHex(parseInt(rgbArr[0]),parseInt(rgbArr[1]),parseInt(rgbArr[2]));
} else if (rgb.match("^rgb") && rgb != "transparent" && rgb.substr(0, 1) != "#") {
rgb = rgb.substr(4, rgb.length - 5);
var rgbArr = rgb.split(",");
rgb = commonCls.getHex(parseInt(rgbArr[0]),parseInt(rgbArr[1]),parseInt(rgbArr[2]));
} else if(rgb.substr(0, 1) != "#"){
if(property_name == "backgroundColor") {
return "transparent";
}
return "";
}
return rgb;
},
colorCheck: function(event) {
if(((event.ctrlKey && !event.altKey) || event.keyCode == 229 || event.keyCode == 46 || event.keyCode == 8 ||
(event.keyCode >= 37 && event.keyCode <= 40) || event.keyCode == 9 || event.keyCode == 13 ||
(event.keyCode >= 96 && event.keyCode <= 105) ||
(event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 65 && event.keyCode <= 70)))
return true;
return false;
},
numberCheck: function(event) {
if(((event.ctrlKey && !event.altKey) || event.keyCode == 229 || event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 13 ||
(event.keyCode >= 96 && event.keyCode <= 105) ||
(event.keyCode >= 37 && event.keyCode <= 40) || (!event.shiftKey && event.keyCode >= 48 && event.keyCode <= 57)))
return true;
return false;
},
numberConvert: function(event) {
if(event.keyCode == 13 || event.type == "blur") {
var event_el = Event.element(event);
var num_value = event_el.value;
var en_num = "0123456789.,-+";
var em_num = "０１２３４５６７８９．，－＋";
var str = "";
for (var i=0; i< num_value.length; i++) {
var c = num_value.charAt(i);
var n = em_num.indexOf(c,0);
var m = en_num.indexOf(c,0);
if (n >= 0) {c = en_num.charAt(n);str += c;
} else if (m >= 0) str += c;
}
if(num_value != str) event_el.value = str;
return true;
}
return false;
},
observe: function(element, name, observer, useCapture, top_el) {
Event.observe(element, name, observer, useCapture, top_el);
},
stopObserving: function(element, name, observer, useCapture) {
Event.stopObserving(element, name, observer, useCapture);
},
stop: function(event) {
Event.stop(event);
},
setStyle: function(el, value) {
Element.setStyle(el, value);
},
hasClassName: function(el, class_name) {
return Element.hasClassName(el,class_name);
},
changeAuthority: function(checkbox, id) {
if (checkbox.type != "checkbox"
|| checkbox.id.length == 0) {
return;
}
var name = checkbox.id.substr(0, checkbox.id.length - id.length);
var ahuthId = name.match(/\d+$/);
name = name.substr(0, name.length - ahuthId.length);
while (checkbox.checked) {
ahuthId++;
var element = $(name + ahuthId + id);
if (element == null) break;
element.checked = true;
}
while (!checkbox.checked) {
ahuthId--;
var element = $(name + ahuthId + id);
if (element == null) break;
element.checked = false;
}
},
load : function(src, check, next, timeout) {
src = src.replace(/&amp;/g,"&");
check = new Function('return !!(' + check + ')');
if (!check()) {
var script = document.createElement('script');
script.src = src;
document.body.appendChild(script);
}
this.wait(check, next, timeout);
},
wait: function  (check, next, timeout) {
if (!check()) {
setTimeout(function() {
if(timeout != undefined) {
timeout = timeout - 100;
if(timeout < 0) return;
}
if (!check()) setTimeout(arguments.callee, 100);
else if(next != null) next();
}, 100);
} else if(next != null)
next();
},
escapeRegExp: function  (str) {
return str.replace(/([\\\/\^\$\*\+\?\{\|\}\[\]])/g,"\\$1");
}
}
commonCls = new clsCommon();
var clsJqcheck = Class.create();
clsJqcheck.prototype = {
initialize: function() {
this.loadedFiles = new Array();
},
jqload: function(dir_name, check, next) {
if(!this.loadedFiles[dir_name]) {
this.loadedFiles[dir_name] = true;
commonCls.load(_nc_core_base_url + _nc_index_file_name + "?action=common_download_js&add_block_flag=1&dir_name=" + dir_name + "&vs=" + _nc_js_vs, check, function(){jQuery.noConflict(); if(next) {next();}});
}
else {
jcheck = new Function('return !!(' + check + ')');
commonCls.wait(jcheck,next);
}
}
}
jqcheckCls = new clsJqcheck();

var clsCommonOperation = Class.create();
var commonOperationCls = Array();
clsCommonOperation.prototype = {
initialize: function(id, unioncolumn_str) {
this.id = id;
this.unioncolumn_str = unioncolumn_str;
},
init: function() {
commonCls.focus($("form"+this.id));
},
selectOnChange: function(event, el) {
if(el == undefined) {
var event_el = Event.element(event);
} else {
var event_el = el;
}
var eleOptions = event_el.getElementsByTagName("option");
var option_len = eleOptions.length;
for (var i = option_len - 1; i >= 0 ; i--){
if (Element.hasClassName(eleOptions[i],"disable_lbl") && eleOptions[i].selected == true){
eleOptions[i].selected = false;
event_el.selectedIndex = 0;
}
}
this.chgDisabled(event_el, "move");
this.chgDisabled(event_el, "copy");
this.chgDisabled(event_el, "shortcut");
},
chgDisabled: function(select_el, mode) {
var operation_el = $(mode + this.id);
if(operation_el) {
if(select_el.selectedIndex == 0) {
operation_el.disabled = true;
} else {
operation_el.disabled = false;
}
}
},
getConfirmMes: function(mes) {
var move_destination_el = $("move_destination"+this.id);
var optText = move_destination_el.options[move_destination_el.selectedIndex].text;
return mes + optText.trim();
},
compBlock: function(event, parent_id_name, main_page_id, mes, mode) {
var move_destination_el = $("move_destination"+this.id);
var value = move_destination_el.value;
if(mode == "move") {
pagesCls.deleteBlock(event, parent_id_name, null, false);
}
commonCls.alert(mes);
if(main_page_id == value || this.unioncolumn_str.match("/|"+value+"|/")) {
setTimeout(function(){location.href = decodeURIComponent(_nc_current_url).unescapeHTML();}, 300);
}
}
}

var compCommonUtil = {
toViewportPosition: function(element) {
return this._toAbsolute(element,true);
},
toDocumentPosition: function(element) {
return this._toAbsolute(element,false);
},
_toAbsolute: function(element,accountForDocScroll) {
if ( navigator.userAgent.toLowerCase().indexOf("msie") == -1 )
return this._toAbsoluteMozilla(element,accountForDocScroll);
var x = 0;
var y = 0;
var parent = element;
while ( parent ) {
var borderXOffset = 0;
var borderYOffset = 0;
if ( parent != element ) {
var borderXOffset = parseInt(this.getElementsComputedStyle(parent, "borderLeftWidth" ));
var borderYOffset = parseInt(this.getElementsComputedStyle(parent, "borderTopWidth" ));
borderXOffset = isNaN(borderXOffset) ? 0 : borderXOffset;
borderYOffset = isNaN(borderYOffset) ? 0 : borderYOffset;
}
x += parent.offsetLeft - parent.scrollLeft + borderXOffset;
y += parent.offsetTop - parent.scrollTop + borderYOffset;
parent = parent.offsetParent;
}
if ( accountForDocScroll ) {
x -= this.docScrollLeft();
y -= this.docScrollTop();
}
return { x:x, y:y };
},
_toAbsoluteMozilla: function(element,accountForDocScroll) {
var x = 0;
var y = 0;
var parent = element;
while ( parent ) {
x += parent.offsetLeft;
y += parent.offsetTop;
parent = parent.offsetParent;
}
parent = element;
while ( parent &&
parent != document.body &&
parent != document.documentElement ) {
if ( parent.scrollLeft  )
x -= parent.scrollLeft;
if ( parent.scrollTop )
y -= parent.scrollTop;
parent = parent.parentNode;
}
if ( accountForDocScroll ) {
x -= this.docScrollLeft();
y -= this.docScrollTop();
}
return { x:x, y:y };
},
docScrollLeft: function() {
if ( window.pageXOffset )
return window.pageXOffset;
else if ( document.documentElement && document.documentElement.scrollLeft )
return document.documentElement.scrollLeft;
else if ( document.body )
return document.body.scrollLeft;
else
return 0;
},
docScrollTop: function() {
if ( window.pageYOffset )
return window.pageYOffset;
else if ( document.documentElement && document.documentElement.scrollTop )
return document.documentElement.scrollTop;
else if ( document.body )
return document.body.scrollTop;
else
return 0;
}
};
compCommonUtil.Effect = {};
compCommonUtil.Effect.SizeAndPosition = Class.create();
compCommonUtil.Effect.SizeAndPosition.prototype = {
initialize: function(element, x, y, w, h, duration, steps, options) {
this.element = $(element);
this.x = x;
this.y = y;
this.w = w;
this.h = h;
this.duration = duration;
this.steps    = steps;
this.options  = arguments[7] || {};
this.sizeAndPosition();
},
sizeAndPosition: function() {
if (this.isFinished()) {
if(this.options.complete) this.options.complete(this);
return;
}
if (this.timer)
clearTimeout(this.timer);
var stepDuration = Math.round(this.duration/this.steps) ;
var currentX = this.element.offsetLeft;
var currentY = this.element.offsetTop;
var currentW = this.element.offsetWidth;
var currentH = this.element.offsetHeight;
this.x = (this.x) ? this.x : currentX;
this.y = (this.y) ? this.y : currentY;
this.w = (this.w) ? this.w : currentW;
this.h = (this.h) ? this.h : currentH;
var difX = this.steps >  0 ? (this.x - currentX)/this.steps : 0;
var difY = this.steps >  0 ? (this.y - currentY)/this.steps : 0;
var difW = this.steps >  0 ? (this.w - currentW)/this.steps : 0;
var difH = this.steps >  0 ? (this.h - currentH)/this.steps : 0;
this.moveBy(difX, difY);
this.resizeBy(difW, difH);
this.duration -= stepDuration;
this.steps--;
this.timer = setTimeout(this.sizeAndPosition.bind(this), stepDuration);
},
isFinished: function() {
return this.steps <= 0;
},
moveBy: function( difX, difY ) {
var currentLeft = this.element.offsetLeft;
var currentTop  = this.element.offsetTop;
var intDifX     = parseInt(difX);
var intDifY     = parseInt(difY);
var style = this.element.style;
if ( intDifX != 0 )
style.left = (currentLeft + intDifX) + "px";
if ( intDifY != 0 )
style.top  = (currentTop + intDifY) + "px";
},
resizeBy: function( difW, difH ) {
var currentWidth  = this.element.offsetWidth;
var currentHeight = this.element.offsetHeight;
var intDifW       = parseInt(difW);
var intDifH       = parseInt(difH);
var style = this.element.style;
if ( intDifW != 0 )
style.width   = (currentWidth  + intDifW) + "px";
if ( intDifH != 0 )
style.height  = (currentHeight + intDifH) + "px";
}
}
compCommonUtil.Effect.Size = Class.create();
compCommonUtil.Effect.Size.prototype = {
initialize: function(element, w, h, duration, steps, options) {
new compCommonUtil.Effect.SizeAndPosition(element, null, null, w, h, duration, steps, options);
}
}
compCommonUtil.Effect.Position = Class.create();
compCommonUtil.Effect.Position.prototype = {
initialize: function(element, x, y, duration, steps, options) {
new compCommonUtil.Effect.SizeAndPosition(element, x, y, null, null, duration, steps, options);
}
}
compCommonUtil.Effect.Round = Class.create();
compCommonUtil.Effect.Round.prototype = {
initialize: function(tagName, className, options) {
var elements = document.getElementsByTagAndClassName(tagName,className);
for ( var i = 0 ; i < elements.length ; i++ )
compCommonUtil.Corner.round( elements[i], options );
}
};

var compTextarea = Class.create();
var textareaComp = Array();
compTextarea.prototype = {
uploadAction    : {},
focus           : false,
popupPrefix     : "",
downloadAction  : "common_download_main",
uploadAction    : {
unique_id : "0",
image     : null,
file      : null
},
top_table       : null,
js_path         : null,
css_path        : null,
textarea        : null,
initialize: function(options) {
var self = this;
self.js_path = _nc_core_base_url + _nc_index_file_name + "?action=common_download_js&add_block_flag=1&dir_name=";
self.css_path = _nc_core_base_url + _nc_index_file_name + "?action=common_download_css&header=0&dir_name=/comp/plugins/";
commonCls.load(self.js_path + "comp_textareamain"+"&vs="+_nc_js_vs, "window.compTextareamain", function(){
self.textarea = new compTextareamain(self);
});
},
textareaShow : function(id, textarea_classname, mode) {
var self = this;
if( this.textarea == null) {
setTimeout(function(){self.textareaShow(id, textarea_classname, mode);}.bind(this), 100);
return;
}
this.setOptions();
this.textarea.textareaShow(id, textarea_classname, mode);
this.setOptionsAfter();
},
textareaEditShow : function(id,text_el,mode) {
var self = this;
if( this.textarea == null) {
setTimeout(function(){self.textareaEditShow(id,text_el,mode);}.bind(this), 100);
return;
}
this.setOptions();
this.textarea.textareaEditShow(id,text_el,mode);
this.setOptionsAfter();
},
clear : function()
{
this.textarea.clear();
},
addFocus : function(now, callback) {
this.textarea.addFocus(now, callback);
},
getTextArea : function() {
return this.textarea.getTextArea();
},
setTextArea : function(newContent) {
var self = this;
if( this.textarea == null) {
setTimeout(function(){self.setTextArea(newContent);}.bind(this), 100);
return;
}
this.textarea.options.content = newContent;
},
focusEditor : function(now, callback) {
if (this.textarea != null || this.textarea != undefined) {
this.textarea.addFocus(now, callback);
}
},
setOptions : function() {
var self = this;
self.textarea.uploadAction = self.uploadAction;
self.textarea.downloadAction = self.downloadAction;
self.textarea.popupPrefix = self.popupPrefix;
self.textarea.focus = self.focus;
},
setOptionsAfter : function() {
var self = this;
this.top_table = this.textarea.el;
}
}
compPopup = Class.create();
compPopup.prototype = {
initialize: function(id, popupID) {
this.id = id;
this.IDPrefix = "popup";
this.popupID = (popupID) ? this.IDPrefix + popupID : this.IDPrefix;
this.IDNone = false;
this.src = null;
this.popupElement = null;
if(this.popupID) {
var popupElement = $(this.popupID);
if(popupElement && popupElement.contentWindow) {
this.popupElement = popupElement;
}
}
this.classNames = new Array("popupIframe");
this.iframeAttributes = {
marginHeight:"0px",
marginWidth:"0px",
frameBorder:"0",
scrolling:"no"
};
this.posCenter = false;
this.position = new Array(null, null);
this.topOverlap = 3;
this.cssFiles = new Array();
this.jsFiles = new Array();
this.observing = true;
this.observer = this._closePopupObserver.bindAsEventListener(this);
this.loadObserver = null;
this.head_title = "Popup Dialog";
this.popup_width = 0;
this.popup_height = 0;
this.modal = true;
this.allowTransparency = false;
this._loadEventFunc = null;
this.showPopupFlag = false;
},
showPopup: function(targetElement, eventElement) {
if(commonCls.referComp["comp_popup"+this.id + this.popupID] == true) {
return;
}
commonCls.referComp["comp_popup"+this.id + this.popupID] = true;
if(targetElement && typeof targetElement != 'string') {
targetElement.style.display = (!(browser.isIE && browser.version < 9) && targetElement.tagName == "TABLE") ? "table" : "block";
}
if (!this.popupElement) {
this.createPopup();
} else {
var visible_flag = true;
if(this.src != null) {
if(this.src != this.popupElement.src) {
this.popupElement.src = this.src;
this.popupElement.contentWindow.document.body.innerHTML = "";
this.popupElement.contentWindow.location.reload();
this.popupElement.style.visibility = "visible";
} else {
this.popupElement.style.visibility = "visible";
}
}
}
if(browser.isGecko || (browser.isIE && browser.version >= 9)) {
if(this.src != null && this.src != this.popupElement.src) this.popupElement.style.visibility = "hidden";
this.popupElement.onload = function() {
this.popupElement.style.visibility = "visible";
setTimeout(function() {
this.resize();
if(this.posCenter) {
commonCls.popup.setPosition(commonCls.getCenterPosition(this.popupElement));
}
}.bind(this), 300);
}.bind(this);
}
if(browser.isSafari) {
this.popupElement.style.visibility = "visible";
setTimeout(function() {
this.resize();
if(this.posCenter) {
commonCls.popup.setPosition(commonCls.getCenterPosition(this.popupElement));
}
}.bind(this), 300);
}
if(this.posCenter) {
} else if (eventElement) {
this.setEventPosition(eventElement);
} else {
this.setPosition(this.position);
}
if (visible_flag && (browser.isIE && browser.version < 9)) {
this.popupElement.style.display = (!(browser.isIE && browser.version < 9) && this.popupElement.tagName == "TABLE") ? "table" : "block";
this.popupElement.style.visibility = "visible";
}
if(this.src == null) {
this.setHTMLText(targetElement);
}
var div = $("_global_modal_dialog");
if(this.modal && !div) {
div = document.createElement("DIV");
div.id = "_global_modal_dialog";
if(this.id) {
var top_el = $(this.id);
}
if(top_el) {
if(top_el.tagName == "TABLE") {
Element.getChildElement(top_el, 3).appendChild(div);
} else {
top_el.appendChild(div);
}
} else {
document.body.appendChild(div);
}
commonCls.showModal(null, div);
}
if(this.observing) {
if(this.modal) {
Event.observe(div, "mousedown", this.observer, false, $(this.id));
} else {
Event.observe(document, "mousedown", this.observer, false, $(this.id));
}
}
commonCls.max_zIndex = commonCls.max_zIndex + 1;
this.popupElement.style.zIndex = commonCls.max_zIndex;
commonCls.referComp["comp_popup"+this.id + this.popupID] = false;
},
showSrcPopup: function(src, eventElement) {
this.src = src;
this.showPopup(null, eventElement);
},
createPopup: function() {
if (!this.IDNone) {
var popupElement = $(this.popupID);
if (popupElement) {
this.popupElement = popupElement;
return;
}
}
this.popupElement = document.createElement("iframe");
if((browser.isIE && browser.version < 9 && browser.version >= 7) || this.allowTransparency)this.popupElement.allowTransparency="true";
if (!this.IDNone) {
this.popupElement.id = this.popupID;
}
for (var i = 0; i < this.classNames.length; i++) {
Element.addClassName(this.popupElement, this.classNames[i]);
}
for (var i in this.iframeAttributes) {
this.popupElement[i] = this.iframeAttributes[i];
}
if(this.id) {
var top_el = $(this.id);
}
if(top_el) {
if(top_el.tagName == "TABLE") {
Element.getChildElement(top_el, 3).appendChild(this.popupElement);
} else {
top_el.appendChild(this.popupElement);
}
} else {
document.body.appendChild(this.popupElement);
}
if(this.src != null) this.popupElement.src = this.src;
},
setHTMLText: function(targetElement) {
var html = document.getElementsByTagName("html")[0];
var htmlAttr = "xmlns=\"" + html.getAttribute("xmlns") + "\" ";
htmlAttr += "xml:lang=\"" + html.getAttribute("xml:lang") + "\" ";
htmlAttr += "lang=\"" + html.getAttribute("lang") + "\"";
var head = document.getElementsByTagName("head")[0];
var links = head.getElementsByTagName("link");
var titleText = "";
var linkText = "";
if(this.head_title != "") {
titleText = "<title>" + this.head_title + "</title>\n"
}
for (var i = 0; i < links.length; i++) {
var link = links[i];
if (link.getAttribute("rel") == "stylesheet") {
linkText += "<link ";
linkText += "rel=\"" + link.getAttribute("rel") + "\" ";
linkText += "type=\"" + "text/css" + "\" ";
if(link.getAttribute("media")) {
linkText += "media=\"" + link.getAttribute("media") + "\" ";
} else {
linkText += "media=\"screen\" ";
}
linkText += "href=\"" + link.getAttribute("href") + "\" ";
linkText += "/>\n";
}
}
for (var i = 0; i < this.cssFiles.length; i++) {
var link = this.cssFiles[i];
linkText += "<link ";
linkText += "rel=\"stylesheet\" ";
linkText += "type=\"text/css\" ";
linkText += "media=\"" + link[1] + "\" ";
linkText += "href=\"" + link[0] + "\" ";
linkText += "/>\n";
}
var text = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
text += "<html " + htmlAttr + ">\n";
text += "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">" + titleText + linkText + "</head>\n";
text += "<body style=\"background-color:transparent;\">\n";
if (typeof targetElement == 'string') {
text += targetElement + "\n";
} else {
if(targetElement.nodeName.toLowerCase() != "script") {
text += "<" + targetElement.nodeName;
for (var i = 0; i < targetElement.attributes.length; i++) {
var attribute = targetElement.attributes[i];
if (attribute.nodeName != "style") {
var value = attribute.nodeValue;
} else {
var value = targetElement.style.cssText;
}
if((browser.isIE && browser.version < 9)) {
if(attribute.nodeName == "_extended" || value == null || value == "" || typeof value=="function") {
continue;
}
}
text += " " + attribute.name + "=\"" + value + "\"";
}
text += ">\n";
text += targetElement.innerHTML.replace(/[\r\n\t]/g, "");
text += "</" + targetElement.nodeName + ">\n";
}
}
text += "</body>\n";
text += "</html>";
text += "<script>";
text += "function popup_resize(){";
text += "var targetElement = document.body.firstChild;";
text += "while (targetElement.nodeType != 1) {";
text += "targetElement = targetElement.nextSibling;";
text += "}";
text += "targetElement.style.display=(!((parent.browser.isIE && parent.browser.version < 9)) && targetElement.tagName=='TABLE')?'table':'block';";
text += "window.frameElement.width = targetElement.offsetWidth + 'px';";
text += "window.frameElement.height = targetElement.offsetHeight + 'px';";
text += "targetElement.style.visibility = 'visible';";
text += "window.frameElement.style.visibility = 'visible';";
text += "}";
text += "setTimeout(popup_resize, 300);";
text += "</script>";
this.popupElement.contentWindow.document.open();
this.popupElement.contentWindow.document.write(text);
this.popupElement.contentWindow.document.close();
this._loadEventFunc = this.loadEvent.bindAsEventListener(this);
if(browser.isIE || browser.isSafari) {
setTimeout(this._loadEventFunc, 500);
} else {
Event.observe(this.popupElement,"load",this._loadEventFunc, false, this.id);
}
},
loadEvent: function(event){
if((browser.isIE && browser.version < 9) || browser.isSafari) {
try{
var tmp = this.popupElement.contentWindow.document;
if(tmp == undefined || tmp == null) {
setTimeout(this._loadEventFunc, 500);
return;
}
} catch(e) {
setTimeout(this._loadEventFunc, 500);
return;
}
}
if(!this.popupElement.contentWindow || !this.popupElement.contentWindow.document ||
this.popupElement.contentWindow.document.body.innerHTML.strip() == "") {
setTimeout(this._loadEventFunc, 500);
return;
}
if(typeof this.loadObserver == 'function') {
this.loadObserver(event);
}
Event.stopObserving(this.popupElement,"load", this._loadEventFunc, false);
},
stopLoadEvent: function(event){
if(this.loadObserver && !((browser.isIE && browser.version < 9) || browser.isSafari)) {
Event.stopObserving(this.popupElement,"load", this._loadEventFunc, false);
}
this.loadObserver = null;
},
_closePopupObserver: function(event){
this.closePopupAll(event);
},
closePopup: function(iframe){
iframe = (iframe == undefined || iframe == null) ? this.popupElement : iframe;
if (iframe) {
var div = $("_global_modal_dialog");
if(div) {
try{
commonCls.stopModal(div);
}catch(e){}
Element.remove(div);
}
if((browser.isIE && browser.version < 9)) iframe.style.display = "none";
iframe.style.visibility = "hidden";
if(browser.isOpera) {
$(this.popupElement).remove();
this.src = null;
this.popupElement = null;
commonCls.referComp["comp_popup"+this.id + this.popupID] = false;
}
}
if(this.observer != null && !(browser.isIE && browser.version >= 9)) {
if(this.modal) {
Event.stopObserving(div, "mousedown", this.observer, false);
} else {
Event.stopObserving(document, "mousedown", this.observer, false);
}
}
},
closePopupAll: function(event){
var iframes = document.body.getElementsByTagName("iframe");
for (var i = 0; i < iframes.length; i++) {
if (iframes[i].id.substr(0, this.IDPrefix.length) == this.IDPrefix && !Element.hasClassName(iframes[i], "visible-hide")) {
this.closePopup(iframes[i]);
}
}
},
resize: function() {
if (!this.popupElement) {
return;
}
try{
var targetElement = this.popupElement.contentWindow.document.body.firstChild;
if(!targetElement) {
return;
}
} catch(e) {
setTimeout(function() {this.resize();}.bind(this), 300);
return;
}
while (targetElement.nodeType != 1) {
targetElement = targetElement.nextSibling;
}
this.popupElement.width = targetElement.offsetWidth + 'px';
this.popupElement.height = targetElement.offsetHeight + 'px';
var position = new Array();
var popupX2 = this.popupElement.offsetLeft + this.popupElement.offsetWidth;
var bodyX2 = Position.getWinOuterWidth() + document.documentElement.scrollLeft;
if (popupX2 > bodyX2) {
position[0] = this.popupElement.offsetLeft - (popupX2 - bodyX2);
if (position[0] < 0) {
position[0] = 0;
}
position[1] = this.popupElement.offsetTop;
this.setPosition(position);
}
},
addClassNames: function(className) {
if (this.popupElement) {
Element.addClassName(this.popupElement, className);
}
this.classNames.push(className);
},
addCSSFiles: function(cssFile, media) {
if (!media) {
media = "all";
}
this.cssFiles.push(new Array(cssFile, media));
},
setIframeAttributes: function(iframeAttributes) {
if (this.popupElement) {
for (var i in iframeAttributes) {
this.popupElement[i] = iframeAttributes[i];
}
}
Object.extend(this.iframeAttributes, iframeAttributes || {});
},
setIDNone: function() {
if (this.popupElement) {
this.popupElement.id = null;
}
this.IDNone = true;
},
setObserving: function(value) {
this.observing = value;
},
setPosition: function(position) {
if (this.popupElement) {
this.popupElement.style.left = position[0] + "px";
this.popupElement.style.top = position[1] + "px";
}
this.position = position;
},
setEventPosition: function(eventElement) {
if(eventElement.tagName == "INPUT" && eventElement.type == "text") {
var offset = Position.cumulativeOffsetScroll(eventElement);
} else {
var offset = Position.positionedOffsetScroll(eventElement);
}
var position = new Array();
position[0] = offset[0];
position[1] = offset[1] + eventElement.offsetHeight - this.topOverlap;
this.setPosition(position);
},
setParentPosition: function(parentElement) {
var offset = Position.cumulativeOffset(parentElement);
this.setPosition(offset);
},
setLapPosition: function(eventElement, parentElement) {
var offset = Position.cumulativeOffset(eventElement);
var parentOffset = Position.cumulativeOffset(parentElement);
var position = new Array();
position[0] = offset[0] + parentOffset[0];
position[1] = offset[1] + parentOffset[1] + eventElement.offsetHeight - this.topOverlap;
this.setPosition(position);
},
setCenterPosition: function(popupElement, targetElement) {
var target_el = $(popupElement);
var center_position = commonCls.getCenterPosition(target_el,targetElement);
this.setPosition(center_position);
},
getPopupElementByEvent: function(eventElement)	{
eventElement = $(eventElement)
targetElement = eventElement.nextSibling;
while (targetElement.nodeType != 1 || !Element.hasClassName(targetElement, "popupClass")) {
targetElement = targetElement.nextSibling;
}
return targetElement;
},
getPopupElement: function()	{
return this.popupElement;
},
isVisible: function()	{
if (this.popupElement && this.popupElement.style.visibility == "visible" && !Element.hasClassName(this.popupElement, 'visible-hide')) {
return true;
}
return false;
},
setTitle: function(head_title_str)	{
this.head_title = head_title_str;
}
}

var compTabset = Class.create();
compTabset.prototype = {
initialize: function(top_el) {
this.top_el = top_el;
this.tab_el = null;
this.tabs = new Array;
this._activeIndex = 0;
},
addTabset: function(label, callbackfunc, initfunc) {
var tab_object = new Object;
tab_object.caption   = label;
tab_object.callbackfunc   = callbackfunc;
tab_object.initfunc   = initfunc;
tab_object.tabLen = this.tabs.length;
this.tabs[tab_object.tabLen] = tab_object;
},
render: function() {
var tab_el = Element.getChildElementByClassName(this.top_el,"comp_tabset");
this.tab_el = tab_el;
if (tab_el) {
tab_el.innerHTML = this.renderTab();
var tableList = tab_el.getElementsByTagName("table");
for (var i = 0; i < tableList.length; i++){
if(Element.hasClassName(tableList[i],"comptabset_tabset")) {
Event.observe(tableList[i],"click",this.tabClick.bindAsEventListener(this),true, this.top_el);
}
}
}
},
renderTab: function() {
var tabset_summary = (commonLang.tabset_summary != undefined) ? commonLang.tabset_summary : "Tabset";
var ret = "";
ret = '<table cellspacing="0" cellpadding="0" class="comptabset_tabs" summary="'+tabset_summary+'"><tr class="comptabset_tabs_tr">';
ret += '<td class="comptabset_linespace"><img src="' + _nc_core_base_url + '/images/common/blank.gif" alt="" title="" class="comptabset_linespace" /></td>';
var content_el = this.tab_el.nextSibling;
for (var i = 0; i < content_el.childNodes.length; i++) {
var container_el = content_el.childNodes[i];
if(container_el == null) {
continue;
}
if(!this.tabs[i] || this.tabs[i].caption == null) {
if(!this.tabs[i] || typeof this.tabs[i] != 'object') {
this.tabs[i] = new Object;
this.tabs[i].callbackfunc   = null;
this.tabs[i].initfunc   = null;
this.tabs[i].tabLen = i;
}
var label_el = Element.getChildElement(container_el);
if(Element.hasClassName(label_el,"comptabset_caption")) {
this.tabs[i].caption   = label_el.innerHTML;
} else {
this.tabs[i].caption   = "Tab"+ (i+1);
}
}
if (i == this._activeIndex) {
var class_name = 'comptabset_active';
if(this.tabs[i].initfunc) {
this.tabs[i].initfunc();
this.tabs[i].initfunc = null;
}
this.tabClick(null, container_el);
} else {
var class_name = '';
commonCls.displayNone(container_el);
}
ret += '<td >'+
'<table' + ' class="comptabset_tabset ' + class_name + '" border="0" summary="">'+
'<tr>'+
'<td class="comptabset_upperleft"></td>'+
'<td class="comptabset_upper"></td>'+
'<td class="comptabset_upperright"></td>'+
'</tr>'+
'<tr>'+
'<td class="comptabset_left"><img src="' + _nc_core_base_url + '/images/common/blank.gif" alt="" title="" class="comptabset_sidespace" /></td>'+
'<td class="comptabset_content"><a class="link" href="#" onclick="return false;">' + this.tabs[i].caption+ '</a></td>'+
'<td class="comptabset_right"><img src="' + _nc_core_base_url + '/images/common/blank.gif" alt="" title="" class="comptabset_sidespace" /></td>'+
'</tr>'+
'</table>'+
'</td><td class="comptabset_linespace"><img src="' + _nc_core_base_url + '/images/common/blank.gif" alt="" title="" class="comptabset_linespace" /></td>';
}
ret += '<td class="comptabset_line"><img src="' + _nc_core_base_url + '/images/common/blank.gif" alt="" title="" class="comptabset_linespace" /></td></tr></table>';
return ret;
},
tabClick: function(event,el) {
var tableList = this.tab_el.getElementsByTagName("table");
var targetEl = (event == undefined || event == null) ? el : Event.element(event);
if(!Element.hasClassName(targetEl,"comptabset_tabset")) {
targetEl = Element.getParentElementByClassName(targetEl,"comptabset_tabset");
}
var count = 0;
for (var i = 0; i < tableList.length; i++){
if(Element.hasClassName(tableList[i],"comptabset_tabset")) {
if(targetEl == tableList[i] || targetEl.parentNode == tableList[i]) {
Element.addClassName(tableList[i],"comptabset_active");
this._activeIndex = count;
} else {
Element.removeClassName(tableList[i],"comptabset_active");
}
count++;
}
}
var content_el = this.tab_el.nextSibling;
for (var i = 0; i < content_el.childNodes.length; i++) {
var container_el = content_el.childNodes[i];
if (!container_el) continue;
if (i == this._activeIndex) {
if(this.tabs[i].initfunc) {
this.tabs[i].initfunc();
this.tabs[i].initfunc = null;
}
commonCls.displayVisible(container_el);
if(this.tabs[i].callbackfunc) {
this.tabs[i].callbackfunc();
}
} else {
commonCls.displayNone(container_el);
}
}
},
setActiveIndex: function(activeIndex) {
this._activeIndex = activeIndex;
},
getActiveIndex: function() {
return this._activeIndex;
},
refresh: function() {
var tableList = this.tab_el.getElementsByTagName("table");
var count = 0;
var activeEl = null;
for (var i = 0; i < tableList.length; i++){
if(Element.hasClassName(tableList[i],"comptabset_tabset")) {
if(this._activeIndex == count) {
activeEl = tableList[i];
break;
}
count++;
}
}
this.tabClick(null,activeEl);
}
}
var compTitleIcon = Class.create();
compTitleIcon.prototype = {
initialize: function(id) {
this.id = id;
this.el = null;
this.hidden = null
this.popup = null;
},
showDialogBox: function(el, hidden) {
this.el = el;
this.hidden = hidden;
commonCls.referComp["compIcon" + this.id] = this;
if(!this.popup) {
this.popup = new compPopup(this.id);
}
var params = new Object();
params["param"] = {
"action":"comp_textarea_view_insertsmiley",
"prefix_id_name":"dialog_insertsmiley",
"parent_id_name":"compIcon" + this.id,
"_noscript":1};
params["top_el"] = this.id;
params["callbackfunc"] = function(res) {
this.popup.showPopup(res, this.el);
}.bind(this);
commonCls.send(params);
},
insertImage: function(params) {
this.el.src = params["f_url"];
this.el.title = params["f_title"];
this.el.alt = params["f_alt"];
var arr = params["f_url"].split("/");
if (arr[arr.length-1] != "blank.gif") {
this.hidden.value = arr[arr.length-2] + "/" + arr[arr.length-1];
} else {
this.hidden.value = "";
}
this.closePopup();
},
closePopup: function() {
if (this.popup) {
this.popup.closePopup();
}
}
}

var compCalendar = Class.create();
var calendarComp = Array();
compCalendar.prototype = {
initialize: function(id, text_el, options) {
this.id = id;
this.popup = null;
this.text_el = null;
this.calendarIcon_el = null;
this.options = {
onClickCallback:       null,
parentFrame:           "",
onClickCallback:       null,
designatedDate:        "",
pre_show_week:         0,
next_show_week:        1,
calendarImgPath:       _nc_core_base_url + "/themes/images/icons",
calendarColorDir:      "default",
calendarImg:           "calendar.gif",
calendarThemeDir:      "default"
};
Object.extend(this.options, options || {});
this.date = null;
this.todayYear = null;
this.todayMonth = null;
this.todayDay = null;
this.selectedYear = null;
this.selectedMonth = null;
this.selectedDay = null;
this.currentYear = null;
this.currentMonth = null;
this.currentDay = null;
this.currentDate = null;
this.setDesignatedDateFlag = false;
this.Mdays = {"01":"31", "02":"28", "03":"31", "04":"30", "05":"31", "06":"30", "07":"31", "08":"31", "09":"30", "10":"31", "11":"30", "12":"31"};
var key = id;
var count = 1;
while (calendarComp[key]) {
key = id + "_" + count;
count++;
}
calendarComp[key] = this;
this.key = key;
this._showCalendarImg(text_el);
},
setDesignatedDate: function(yyyymmdd) {
this.options.designatedDate = yyyymmdd;
},
onMoveClick: function(yyyymmdd) {
this.currentYear = yyyymmdd.substr(0, 4);
this.currentMonth = yyyymmdd.substr(4, 2);
this.currentDay = yyyymmdd.substr(6, 2);
this.currentDate = yyyymmdd;
var popup_el = this.popup.getPopupElement();
popup_el.contentWindow.document.body.innerHTML = this._render();
this.popup.resize();
},
onDayClick: function(yyyymmdd) {
var day_separator = (compCalendarLang.day_separator != undefined) ? compCalendarLang.day_separator : "/";
if(this.options.onClickCallback != null) {
this.options.onClickCallback(yyyymmdd);
} else {
if(this.text_el.tagName.toLowerCase() == 'input' && this.text_el.type.toLowerCase() == 'text') {
this.text_el.value = yyyymmdd.substr(0, 4)+ day_separator + yyyymmdd.substr(4, 2) + day_separator + yyyymmdd.substr(6, 2);
commonCls.focus(this.text_el);
}
}
this._popupClose();
},
disabledCalendar: function(value) {
this.text_el.disabled = value;
if(value) {
this.text_el.blur();
this.calendarIcon_el.blur();
Element.addClassName(this.calendarIcon_el, "display-none");
if (this.popup != null) {
this.popup.closePopup(this.popup.getPopupElement());
}
} else {
Element.removeClassName(this.calendarIcon_el, "display-none");
commonCls.focus(this.text_el);
}
},
_showCalendarImg: function(text_el) {
if(typeof text_el == 'string') {
if(this.options.parentFrame == "" || this.options.parentFrame == null) {
text_el = $(text_el);
} else {
text_el = this.options.parentFrame.contentWindow.document.getElementById(text_el);
}
}
if(typeof text_el != 'object') return;
if(this.options.parentFrame == "" || this.options.parentFrame == null) {
var calendarA_el = document.createElement("a");
var calendarImg_el = document.createElement("img");
} else {
var calendarA_el = this.options.parentFrame.contentWindow.document.createElement("a");
var calendarImg_el = this.options.parentFrame.contentWindow.document.createElement("img");
}
calendarA_el.href = "#";
calendarImg_el.src = this.options.calendarImgPath + "/" +  this.options.calendarColorDir + "/" + this.options.calendarImg;
Element.addClassName(calendarA_el, "comp_calendar_icon");
if(text_el.tagName.toLowerCase() == 'input' && text_el.type.toLowerCase() == 'text') {
Element.addClassName(text_el, "comp_calendar_text");
}
calendarImg_el.alt = (compCalendarLang.icon_alt != undefined) ? compCalendarLang.icon_alt : "Calendar";
calendarImg_el.title = (compCalendarLang.icon_title != undefined) ? compCalendarLang.icon_title : "Show Calendar";
text_el.parentNode.insertBefore(calendarA_el, text_el);
text_el.parentNode.insertBefore(text_el, calendarA_el);
calendarA_el.appendChild(calendarImg_el);
Event.observe(calendarA_el, "click",
function(event){
this._showCalendar();
Event.stop(event);
}.bindAsEventListener(this), false, this.id);
this.text_el = text_el;
this.calendarIcon_el = calendarA_el;
},
_showCalendar: function() {
this.date = new Date();
this.todayYear = this._getFormat(this.date.getFullYear());
this.todayMonth = this._getFormat(this.date.getMonth() + 1);
this.todayDay = this._getFormat(this.date.getDate());
if(this.setDesignatedDateFlag == true) {
this.options.designatedDate = null;
this.setDesignatedDateFlag = false;
}
if((this.options.designatedDate == null || this.options.designatedDate == "") &&
this.text_el.tagName.toLowerCase() == 'input' && this.text_el.type.toLowerCase() == 'text') {
var sel_date = this.text_el.value;
if(sel_date.length == 10) {
var sel_year = sel_date.substr(0, 4);
var sel_month = sel_date.substr(5, 2);
var sel_day = sel_date.substr(8, 2);
if(valueParseInt(sel_month) > 0 && valueParseInt(sel_month) < 13 &&
valueParseInt(sel_day) > 0 && valueParseInt(sel_day) < 32) {
this.setDesignatedDateFlag = true;
this.options.designatedDate = sel_year + sel_month + sel_day;
}
}
}
if(this.options.designatedDate == null || this.options.designatedDate == "") {
this.selectedYear = null;
this.selectedMonth = null;
this.selectedDay = null;
this.currentYear = this.todayYear;
this.currentMonth = this.todayMonth;
this.currentDay = "01";
} else {
this.selectedYear = this.options.designatedDate.substr(0, 4);
this.selectedMonth = this.options.designatedDate.substr(4, 2);
this.selectedDay = this.options.designatedDate.substr(6, 2);
this.currentYear = this.selectedYear;
this.currentMonth = this.selectedMonth;
this.currentDay = "01";
}
this.currentDate = this.currentYear + this.currentMonth + this.currentDay;
var html = this._render();
if(!this.popup) {
this.popup = new compPopup(this.id,  "compCalendar");
var new_dir_name ="/comp/"+this.options.calendarThemeDir+"/comp_calendar.css";
var css_name = _nc_core_base_url + _nc_index_file_name + "?action=common_download_css&dir_name="+new_dir_name+"&header=0";
this.popup.addCSSFiles(css_name);
this.popup.observer = function(event) {this._popupClose(); }.bind(this);
}
if(this.options.parentFrame) {
this.popup.setLapPosition(this.calendarIcon_el, this.options.parentFrame);
this.popup.showPopup(html);
} else {
this.popup.showPopup(html, this.calendarIcon_el);
}
},
_getNextYear: function(yyyy, mm, dd) {
yyyy = valueParseInt(yyyy) + 1;
return this._getFormat(yyyy) + this._getFormat(mm) + this._getFormat(dd);
},
_getPrevYear: function(yyyy, mm, dd) {
yyyy = valueParseInt(yyyy) - 1;
if(yyyy < 1900) {
yyyy = 1900;
}
return this._getFormat(yyyy) + this._getFormat(mm) + this._getFormat(dd);
},
_getNextDate: function(yyyy, mm, dd) {
mm = valueParseInt(mm) + 1;
if(mm == 13) {
mm = 1;
yyyy = valueParseInt(yyyy) + 1;
}
return this._getFormat(yyyy) + this._getFormat(mm) + this._getFormat(dd);
},
_getPrevDate: function(yyyy, mm, dd) {
mm = valueParseInt(mm) - 1;
if(mm <= 0) {
mm = 12;
yyyy = valueParseInt(yyyy) - 1;
}
return this._getFormat(yyyy) + this._getFormat(mm) + this._getFormat(dd);
},
_getFormat: function(num) {
return (valueParseInt(num) < 10) ? ("0" + valueParseInt(num)) : "" + num;
},
_getMonthDays: function(yy, mm) {
if(mm == "02") {
if ((yy % 4) == 0) {
return "29";
} else if ((yy % 100) == 0) {
return "28";
} else if ((yy % 400) == 0) {
return "29";
}
}
return this.Mdays[mm];
},
_getWeekDays: function(yyyy,mm,dd) {
var now = new Date(valueParseInt(yyyy), valueParseInt(mm) - 1, valueParseInt(dd));
var w = now.getDay();
return w;
},
_render: function() {
var next_year = this._getNextYear(this.currentYear, this.currentMonth, this.currentDay);
var prev_year = this._getPrevYear(this.currentYear, this.currentMonth, this.currentDay);
var next_month = this._getNextDate(this.currentYear, this.currentMonth, this.currentDay);
var prev_month = this._getPrevDate(this.currentYear, this.currentMonth, this.currentDay);
var pre_end_date = this._getMonthDays(prev_month.substr(2, 2), prev_month.substr(4, 2));
var end_date = this._getMonthDays(this.currentYear.substr(2, 2), this.currentMonth);
var start_w = this._getWeekDays(this.currentYear, this.currentMonth, this.currentDay);
if(start_w == 0) {
if(this.options.pre_show_week != 0) {
var pre_start_date = pre_end_date - 7*this.options.pre_show_week + 1;
} else {
var pre_start_date = 0;
}
} else {
var pre_start_date = pre_end_date - 7*this.options.pre_show_week - (start_w - 1);
}
var loop_week = valueParseInt(Math.ceil((valueParseInt(end_date) + start_w + 7*this.options.pre_show_week + 7*this.options.next_show_week)  / 7));
var currentMonth = valueParseInt(this.currentMonth);
switch (currentMonth) {
case 1: currentMonth = compCalendarLang.month_jan; break;
case 2: currentMonth = compCalendarLang.month_feb; break;
case 3: currentMonth = compCalendarLang.month_mar; break;
case 4: currentMonth = compCalendarLang.month_apr; break;
case 5: currentMonth = compCalendarLang.month_may; break;
case 6: currentMonth = compCalendarLang.month_jun; break;
case 7: currentMonth = compCalendarLang.month_jul; break;
case 8: currentMonth = compCalendarLang.month_aug; break;
case 9: currentMonth = compCalendarLang.month_sep; break;
case 10: currentMonth = compCalendarLang.month_oct; break;
case 11: currentMonth = compCalendarLang.month_nov; break;
case 12: currentMonth = compCalendarLang.month_dec; break;
default:
}
var html =
"<table class=\"compcalendar_top\" summary=\"\"><tr><td class=\"compcalendar_top_td\">" +
"<table border=\"0\" class=\"compcalendar\" summary=\"" + compCalendarLang.summary + "\">" +
"<tr>" +
"<td class=\"compcalendar_title\" colspan=\"7\">" +
this.currentYear +
compCalendarLang.year +
"&nbsp;" +
currentMonth +
"</td>" +
"</tr>" +
"<tr class=\"compcalendar_button\">" +
"<td>" +
"<a class=\"compcalendar_btnlink\" href=\"#\" onclick=\"parent.calendarComp['" +  this.key + "'].onMoveClick('" + prev_year + "'); return false;\" title=\"" + compCalendarLang.title_prev_year + "\">" +
compCalendarLang.btn_prev_year +
"</a>" +
"</td>" +
"<td>" +
"<a class=\"compcalendar_btnlink\" href=\"#\" onclick=\"parent.calendarComp['" +  this.key + "'].onMoveClick('" + prev_month + "'); return false;\" title=\"" + compCalendarLang.title_prev_month + "\">" +
compCalendarLang.btn_prev_month +
"</a>" +
"</td>" +
"<td colspan=\"3\">" +
"<a class=\"compcalendar_btnlink\" href=\"#\" onclick=\"parent.calendarComp['" +  this.key + "'].onMoveClick('" + this.todayYear + this.todayMonth + "01" + "'); return false;\" title=\"" + compCalendarLang.title_today + "\">" +
compCalendarLang.move_today +
"</a>" +
"</td>" +
"<td>" +
"<a class=\"compcalendar_btnlink\" href=\"#\" onclick=\"parent.calendarComp['" +  this.key + "'].onMoveClick('" + next_month + "'); return false;\" title=\"" + compCalendarLang.title_next_month + "\">" +
compCalendarLang.btn_next_month +
"</a>" +
"</td>" +
"<td>" +
"<a class=\"compcalendar_btnlink\" href=\"#\" onclick=\"parent.calendarComp['" +  this.key + "'].onMoveClick('" + next_year + "'); return false;\" title=\"" + compCalendarLang.title_next_year + "\">" +
compCalendarLang.btn_next_year +
"</a>" +
"</td>" +
"</tr>" +
"<tr class=\"compcalendar_week\">" +
"<td class=\"compcalendar_sun\">" +
compCalendarLang.week_sun +
"</td>" +
"<td>" +
compCalendarLang.week_mon +
"</td>" +
"<td>" +
compCalendarLang.week_tue +
"</td>" +
"<td>" +
compCalendarLang.week_wed +
"</td>" +
"<td>" +
compCalendarLang.week_thu +
"</td>" +
"<td>" +
compCalendarLang.week_fri +
"</td>" +
"<td class=\"compcalendar_sat\">" +
compCalendarLang.week_sat +
"</td>" +
"</tr>";
var pre_outside_day = pre_start_date;
var current_day = 1;
var post_end_date = 1;
for (var i = 0; i < loop_week; i++) {
html += "<tr class=\"compcalendar_day\">";
for (var j = 0; j < 7; j++) {
if(pre_outside_day > 0) {
var day_class = "compcalendar_outside";
var day = pre_outside_day;
if(pre_end_date < day+1) {
pre_outside_day = 0
} else {
pre_outside_day++;
}
var prefix_day_click = prev_month.substr(0, 6);
} else if(current_day > 0) {
var day = current_day;
if (end_date < day+1) {
current_day = 0;
} else {
current_day++;
}
if (j == 0) {
var day_class="compcalendar_sun";
} else if(j == 6) {
var day_class="compcalendar_sat";
} else {
var day_class="compcalendar_weekday";
}
if (this.currentYear == this.selectedYear && this.currentMonth == this.selectedMonth && this.selectedDay == day) {
day_class += " compcalendar_highlight";
}
if (this.currentYear == this.todayYear && this.currentMonth == this.todayMonth && this.todayDay == day) {
day_class += " compcalendar_today";
}
var prefix_day_click = this.currentYear + this.currentMonth;
} else {
var day_class = "compcalendar_outside";
var day = post_end_date;
post_end_date++
var prefix_day_click = next_month.substr(0, 6);
}
html += "<td class=\"" + day_class + "\">" +
"<a href=\"#\" onclick=\"parent.calendarComp['" +  this.key + "'].onDayClick('" + prefix_day_click + this._getFormat(day) + "'); return false;\" class=\"compcalendar_link\">" +
day +
"</a>" +
"</td>";
}
html += "</tr>";
}
html += "</table>" +
"</td></tr></table>";
return html;
},
_popupClose: function() {
this.popup.closePopup(this.popup.getPopupElement());
if(this.text_el.tagName.toLowerCase() == 'input' && this.text_el.type.toLowerCase() == 'text') {
commonCls.focus(this.text_el);
}
}
}
var compColor = Class.create();
compColor.prototype = {
initialize: function(id) {
this.id = id;
this.el = null;
this.popup = null;
},
showDialogBox: function(el, hidden) {
this.el = el;
this.hidden = hidden;
commonCls.referComp[this.id] = this;
commonCls.referComp["compIcon" + this.id] = this;
if(!this.popup) {
this.popup = new compPopup(this.id);
}
var params = new Object();
params["param"] = {
"action":"comp_textarea_view_selectcolor",
"prefix_id_name":"dialog_selectcolor",
"parent_id_name":"compIcon" + this.id,
"_noscript":1};
params["top_el"] = this.id;
params["callbackfunc"] = function(res) {
this.popup.showPopup(res, this.el);
}.bind(this);
commonCls.send(params);
},
setColor: function($dummy, params) {
this.el.style.backgroundColor = params["color"];
this.hidden.value = params["color"];
this.closePopup();
},
closePopup: function() {
if(this.popup) {
this.popup.closePopup();
}
}
}

compDragAndDrop = Class.create();
compDragAndDrop.prototype = {
initialize: function() {
this.dropZones                = new Array();
this.draggables               = new Array();
this.currentDragObjects       = new Array();
this.dragElement              = null;
this.lastSelectedDraggable    = null;
this.currentDragObjectVisible = false;
this.interestedInMotionEvents = false;
this._mouseDown = this._mouseDownHandler.bindAsEventListener(this);
this._mouseMove = this._mouseMoveHandler.bindAsEventListener(this);
this._mouseUp = this._mouseUpHandler.bindAsEventListener(this);
this.dragElementPosition = null;
this.add_absolute = false;
this.dragParentElement = null;
this.dummyElement = null;
this.dragObjectTransparent = true;
this.draggableRangeElement = null;
this.draggableRangeElementOffset = null;
this.origPos = null;
this.startx = null;
this.starty = null;
this.dragElementWidth = null;
this.scrollMovePx = 30;
},
registerDropZone: function(aDropZone, dropObjectAppendChild) {
if (aDropZone.tagName != undefined) {
aDropZone = new compDropzone(aDropZone,null, dropObjectAppendChild);
}
if (dropObjectAppendChild == true || dropObjectAppendChild == false) {
aDropZone.setDropObjectAppendChild(dropObjectAppendChild);
}
this.dropZones[ this.dropZones.length ] = aDropZone;
},
deregisterDropZone: function(aDropZone) {
var newDropZones = new Array();
var j = 0;
for ( var i = 0 ; i < this.dropZones.length ; i++ ) {
if ( this.dropZones[i] != aDropZone )
newDropZones[j++] = this.dropZones[i];
}
this.dropZones = newDropZones;
},
clearDropZones: function() {
this.dropZones = new Array();
},
registerDraggableRange: function(el) {
el = $(el);
this.draggableRangeElement = el;
},
registerDraggable: function( aDraggable,  dragObjectTransparent) {
if (dragObjectTransparent == true || dragObjectTransparent == false) {
this.dragObjectTransparent = dragObjectTransparent;
}
if (aDraggable.tagName != undefined) {
aDraggable = new compDraggable(aDraggable);
}
this.draggables[ this.draggables.length ] = aDraggable;
this._addMouseDownHandler( aDraggable );
},
clearSelection: function() {
for ( var i = 0 ; i < this.currentDragObjects.length ; i++ ) {
this.currentDragObjects[i].deselect();
}
this.currentDragObjects = new Array();
this.lastSelectedDraggable = null;
},
hasSelection: function() {
return this.currentDragObjects.length > 0;
},
setStartDragFromElement: function( e, mouseDownElement ) {
var offset = Position.cumulativeOffsetScroll(mouseDownElement);
this.origPos = new Object();
this.draggableRangeElementOffset = new Object();
this.origPos.x = offset[0];
this.origPos.y = offset[1];
if(Element.getStyle(mouseDownElement, "position") == "relative") {
this.draggableRangeElementOffset.x = valueParseInt(Element.getStyle(mouseDownElement, "left"));
this.draggableRangeElementOffset.y = valueParseInt(Element.getStyle(mouseDownElement, "top"))
this.origPos.x -= this.draggableRangeElementOffset.x;
this.origPos.y -= this.draggableRangeElementOffset.y;
} else {
this.draggableRangeElementOffset.x = 0;
this.draggableRangeElementOffset.y = 0;
}
this.startx = Event.pointerX(e) - this.origPos.x;
this.starty = Event.pointerY(e) - this.origPos.y;
this.interestedInMotionEvents = this.hasSelection();
this._terminateEvent(e);
},
updateSelection: function( draggable, extendSelection ) {
if ( ! extendSelection )
this.clearSelection();
if ( draggable.isSelected() ) {
this.currentDragObjects.removeItem(draggable);
draggable.deselect();
if ( draggable == this.lastSelectedDraggable )
this.lastSelectedDraggable = null;
} else {
this.currentDragObjects[ this.currentDragObjects.length ] = draggable;
draggable.select();
this.lastSelectedDraggable = draggable;
}
},
_mouseDownHandler: function(e) {
if ( arguments.length == 0 )
e = event;
var nsEvent = e.which != undefined;
if ( (nsEvent && e.which != 1) || (!nsEvent && e.button != 1))
return;
var eventTarget      = Event.element(e);
var draggableObject  = eventTarget.draggableObject;
if(typeof draggableObject != 'object') draggableObject = null;
var candidate = eventTarget;
while (draggableObject == null && candidate.parentNode) {
candidate = candidate.parentNode;
draggableObject = candidate.draggableObject;
}
if ( typeof draggableObject != 'object' )
return;
this.updateSelection( draggableObject, e.ctrlKey );
if ( this.hasSelection() ) {
for ( var i = 0 ; i < this.dropZones.length ; i++ ) {
this.dropZones[i].clearPositionCache();
}
}
this.setStartDragFromElement( e, draggableObject.getMouseDownHTMLElement() );
},
_mouseMoveHandler: function(e) {
var nsEvent = e.which != undefined;
if ( !this.interestedInMotionEvents ) {
return;
}
if ( ! this.hasSelection() )
return;
if ( ! this.currentDragObjectVisible ) {
this._startDrag(e);
}
if ( !this.activatedDropZones )
this._activateRegisteredDropZones();
this._updateDraggableLocation(e);
this._updateDropZonesHover(e);
this._terminateEvent(e);
},
_makeDraggableObjectVisible: function(e)
{
if ( !this.hasSelection() )
return;
var dragElement;
if ( this.currentDragObjects.length > 1 )
dragElement = this.currentDragObjects[0].getMultiObjectDragGUI(this.currentDragObjects);
else
dragElement = this.currentDragObjects[0].getSingleObjectDragGUI();
if(valueParseInt(dragElement.style.width) > 0) {
this.dragElementWidth = dragElement.style.width;
} else {
this.dragElementWidth = 0;
}
dragElement.style.width = dragElement.offsetWidth + "px";
if(dragElement.tagName.toLowerCase() == 'tr') {
var tag_kind = "tr";
} else if(dragElement.tagName.toLowerCase() == 'td') {
var tag_kind = "td";
} else {
var tag_kind = "other";
}
if(tag_kind == 'tr' || tag_kind == 'td') {
var table = document.createElement("table");
var parentTable = dragElement.parentNode;
while(parentTable.tagName.toLowerCase() != "table") {
parentTable = parentTable.parentNode;
if(parentTable.tagName.toLowerCase() == "body") {
break;
}
}
table.className = parentTable.className;
var append_el = document.createElement("tbody");
table.appendChild(append_el);
if(tag_kind == 'td') {
var append_el = document.createElement("tr");
append_el.className = dragElement.parentNode.className;
table.appendChild(append_el);
new Insertion.After(dragElement, "<td />");
} else {
new Insertion.After(dragElement, "<tr />");
}
this.dragParentElement = table;
} else {
var append_el = document.createElement("div");
new Insertion.After(dragElement, "<input type='hidden' value='' />");
this.dragParentElement = append_el;
}
this.dummyElement = dragElement.nextSibling;
commonCls.max_zIndex = commonCls.max_zIndex + 1;
append_el.appendChild(dragElement);
if(this.dragObjectTransparent) {
if(browser.isIE || browser.isSafari) {
var buf_el = append_el.cloneNode(true);
var inputList = buf_el.getElementsByTagName("input");
for (var i = 0; i < inputList.length; i++){
var type = inputList[i].getAttribute("type");
if(type == "text" || type == "hidden") {
inputList[i].setAttribute("value",'',0);
}
}
var dragHtml = buf_el.innerHTML;
if(browser.isIE) Element.remove(buf_el);
else Element.remove(buf_el.childNodes[0]);
buf_el = null;
} else {
var dragHtml = append_el.innerHTML;
}
new Insertion.After(this.dummyElement, dragHtml);
var bufEl = this.dummyElement.nextSibling;
if(this.dragElementWidth == null || this.dragElementWidth == 0) {
bufEl.style.width = '';
}
Element.addClassName(bufEl, "_draganddrop_transparent");
Element.remove(this.dummyElement);
this.dummyElement = bufEl;
}
if(tag_kind == 'tr' || tag_kind == 'td') {
var dragElementPosition = table;
} else {
var dragElementPosition = dragElement.parentNode;
}
dragElementPosition.style.zIndex = commonCls.max_zIndex;
this.add_absolute = false;
if ( Element.getStyle(dragElementPosition, "position")  != "absolute" ) {
dragElementPosition.style.position = "absolute";
this.add_absolute = true;
}
if(tag_kind == 'tr' || tag_kind == 'td') {
document.body.appendChild(table);
} else {
document.body.appendChild(append_el);
}
this.dragElement = dragElement;
this.dragElementPosition = dragElementPosition;
this._updateDraggableLocation(e);
this.currentDragObjectVisible = true;
},
_leftOffset: function(e) {
return e.offsetX ? (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft) : 0
},
_topOffset: function(e) {
return e.offsetY ? (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) :0
},
_updateDraggableLocation: function(e) {
var dragObjectStyle = this.dragElementPosition.style;
if(this.draggableRangeElement != null) {
var drop_offset = Position.cumulativeOffset(this.draggableRangeElement);
drop_offset[0] -= this.draggableRangeElementOffset.x;
drop_offset[1] -= this.draggableRangeElementOffset.y;
var drop_el_left = drop_offset[0];
var drop_el_right = drop_offset[0] + this.draggableRangeElement.offsetWidth;
var drop_el_top = drop_offset[1];
var drop_el_bottom = drop_offset[1] + this.draggableRangeElement.offsetHeight;
var offset = Position.cumulativeOffset(this.dragElementPosition);
var el_left = offset[0];
var el_right = offset[0] + this.dragElementPosition.offsetWidth;
var el_top = offset[1];
var el_bottom = offset[1] + this.dragElementPosition.offsetHeight;
var buf_el_left = (Event.pointerX(e) - this.startx);
var buf_el_right = (Event.pointerX(e) - this.startx) + this.dragElementPosition.offsetWidth;
var buf_el_top = (Event.pointerY(e) - this.starty);
var buf_el_bottom = (Event.pointerY(e) - this.starty) + this.dragElementPosition.offsetHeight;
if(buf_el_left < drop_el_left) {
dragObjectStyle.left = valueParseInt(dragObjectStyle.left) + (drop_el_left - el_left) + "px";
} else if(buf_el_right > drop_el_right){
dragObjectStyle.left = valueParseInt(dragObjectStyle.left) + (drop_el_right - el_right) + "px";
} else {
dragObjectStyle.left = buf_el_left + "px";
}
if(buf_el_top < drop_el_top) {
dragObjectStyle.top = valueParseInt(dragObjectStyle.top) + (drop_el_top - el_top) + "px";
} else if(buf_el_bottom > drop_el_bottom){
dragObjectStyle.top = valueParseInt(dragObjectStyle.top) + (drop_el_bottom - el_bottom) + "px";
} else {
dragObjectStyle.top  = buf_el_top + "px";
}
} else {
dragObjectStyle.left = (Event.pointerX(e) - this.startx) + "px";
dragObjectStyle.top  = (Event.pointerY(e) - this.starty) + "px";
}
commonCls.scrollMoveDrag(e, this.scrollMovePx);
},
_updateDropZonesHover: function(e) {
var n = this.dropZones.length;
for ( var i = 0 ; i < n ; i++ ) {
if ( ! this._mousePointInDropZone( e, this.dropZones[i] ) )
this.dropZones[i].hideHover(e);
}
for ( var i = 0 ; i < n ; i++ ) {
if ( this._mousePointInDropZone( e, this.dropZones[i] ) ) {
if ( this.dropZones[i].canAccept(this.currentDragObjects) )
this.dropZones[i].showHover(e);
}
}
},
_startDrag: function(e) {
for ( var i = 0 ; i < this.currentDragObjects.length ; i++ ) {
this.currentDragObjects[i].prestartDrag();
}
this._makeDraggableObjectVisible(e);
for ( var i = 0 ; i < this.currentDragObjects.length ; i++ ) {
this.currentDragObjects[i].startDrag();
}
},
_mouseUpHandler: function(e) {
Event.stopObserving(document, "mousemove", this._mouseMove);
Event.stopObserving(document, "mouseup",  this._mouseUp);
if ( ! this.hasSelection() ){
return;
}
var nsEvent = e.which != undefined;
if ( (nsEvent && e.which != 1) || (!nsEvent && e.button != 1)) {
return;
}
this.interestedInMotionEvents = false;
if ( this.dragElementPosition == null ) {
this._terminateEvent(e);
return;
}
if ( this._placeDraggableInDropZone(e) ) {
this._completeDropOperation(e);
} else {
this._terminateEvent(e);
new compCommonUtil.Effect.Position( this.dragElementPosition,
this._leftOffset(e) + this.origPos.x - valueParseInt(Element.getStyle(Element.getChildElement(this.dragElementPosition), "marginLeft")),
this._topOffset(e) + this.origPos.y - valueParseInt(Element.getStyle(Element.getChildElement(this.dragElementPosition), "marginTop")),
200,
20,
{ complete : this._doCancelDragProcessing.bind(this) } );
}
},
_retTrue: function () {
return true;
},
_completeDropOperation: function(e) {
Element.remove(this.dragParentElement);
Element.remove(this.dummyElement);
this._deactivateRegisteredDropZones();
this._endDrag();
this.clearSelection();
this.dragElement = null;
this.dragElementPosition = null;
this.currentDragObjectVisible = false;
this._terminateEvent(e);
},
_doCancelDragProcessing: function() {
this._cancelDrag();
this.clearSelection();
if(this.dragElement == null) return;
if(this.add_absolute) {
this.dragElement.style.position = "";
}
if(this.dragElementWidth != null && this.dragElementWidth > 0) {
this.dragElement.style.width = this.dragElementWidth + "px";
} else {
this.dragElement.style.width = '';
}
this.dragElementWidth = null;
new Insertion.After(this.dummyElement, "<input type='hidden' value='' />");
var buf_dummy_el = this.dummyElement.nextSibling;
Element.remove(this.dummyElement);
this.dummyElement = buf_dummy_el;
var parent_el = Element.getParentElement(this.dummyElement);
parent_el.insertBefore(this.dragElement, this.dummyElement);
Element.remove(this.dragParentElement);
Element.remove(this.dummyElement);
this._deactivateRegisteredDropZones();
this.dragElement = null;
this.dragElementPosition = null;
this.currentDragObjectVisible = false;
},
_placeDraggableInDropZone: function(e) {
var foundDropZone = false;
var n = this.dropZones.length;
for ( var i = 0 ; i < n ; i++ ) {
if ( this._mousePointInDropZone( e, this.dropZones[i] ) ) {
if ( this.dropZones[i].canAccept(this.currentDragObjects) ) {
foundDropZone = this.dropZones[i].save(this.currentDragObjects);
if(foundDropZone) {
this.dropZones[i].accept(this.currentDragObjects);
this.dropZones[i].hideHover(e);
} else {
this.dropZones[i].hideHover(e);
continue;
}
break;
}
}
}
return foundDropZone;
},
_cancelDrag: function() {
for ( var i = 0 ; i < this.currentDragObjects.length ; i++ )
this.currentDragObjects[i].cancelDrag();
},
_endDrag: function() {
for ( var i = 0 ; i < this.currentDragObjects.length ; i++ )
this.currentDragObjects[i].endDrag();
},
_mousePointInDropZone: function( e, dropZone ) {
var absoluteRect = dropZone.getAbsoluteRect();
if(this.dragElement == dropZone.getHTMLElement()) {
return false;
}
Position.prepare();
var pointerX = Event.pointerX(e);
var pointerY = Event.pointerY(e);
if(this.draggableRangeElement != null) {
var drop_offset = Position.cumulativeOffset(this.draggableRangeElement);
var drop_el_left = drop_offset[0];
var drop_el_right = drop_offset[0] + this.draggableRangeElement.offsetWidth;
var drop_el_top = drop_offset[1];
var drop_el_bottom = drop_offset[1] + this.draggableRangeElement.offsetHeight;
if(pointerX < drop_el_left) {
pointerX = drop_el_left + 1;
} else if(pointerX > drop_el_right){
pointerX = drop_el_right - 1;
}
if(pointerY < drop_el_top) {
pointerY = drop_el_top + 1;
} else if(pointerY > drop_el_bottom){
pointerY = drop_el_bottom - 1;
}
}
return pointerX  > absoluteRect.left &&
pointerX  < absoluteRect.right &&
pointerY  > absoluteRect.top   &&
pointerY  < absoluteRect.bottom;
},
_addMouseDownHandler: function( aDraggable )
{
htmlElement  = aDraggable.getMouseDownHTMLElement();
if ( htmlElement  != null ) {
htmlElement.draggableObject = aDraggable;
Event.observe(htmlElement , "mousedown", this._onmousedown.bindAsEventListener(this));
Event.observe(htmlElement, "mousedown", this._mouseDown);
}
},
_activateRegisteredDropZones: function() {
var n = this.dropZones.length;
for ( var i = 0 ; i < n ; i++ ) {
var dropZone = this.dropZones[i];
if ( dropZone.canAccept(this.currentDragObjects) )
dropZone.activate();
}
this.activatedDropZones = true;
},
_deactivateRegisteredDropZones: function() {
var n = this.dropZones.length;
for ( var i = 0 ; i < n ; i++ )
this.dropZones[i].deactivate();
this.activatedDropZones = false;
},
_onmousedown: function () {
Event.observe(document, "mousemove", this._mouseMove);
Event.observe(document, "mouseup",  this._mouseUp);
},
_terminateEvent: function(e) {
if ( e.stopPropagation != undefined )
e.stopPropagation();
else if ( e.cancelBubble != undefined )
e.cancelBubble = true;
if ( e.preventDefault != undefined )
e.preventDefault();
else
e.returnValue = false;
}
};
compDraggable = Class.create();
compDraggable.prototype = {
initialize: function( htmlElement, dragElement, params, type ) {
this.type          = type;
this.htmlElement   = $(htmlElement);
if(dragElement == undefined || dragElement == null) {
this.dragElement   = this.htmlElement;
} else {
this.dragElement   = dragElement;
}
this.selected      = false;
this.params = params;
},
getMouseDownHTMLElement: function() {
return this.dragElement;
},
getHTMLElement: function() {
return this.htmlElement;
},
select: function() {
this.selected = true;
if ( this.showingSelected )
return;
this.showingSelected = true;
},
deselect: function() {
this.selected = false;
if ( !this.showingSelected )
return;
this.showingSelected = false;
},
isSelected: function() {
return this.selected;
},
startDrag: function() {
var draggable = this.htmlElement;
Element.setStyle(draggable, {opacity:0.7});
},
prestartDrag: function() {
},
cancelDrag: function() {
var draggable = this.htmlElement;
Element.setStyle(draggable, {opacity:""});
},
endDrag: function() {
var draggable = this.htmlElement;
Element.setStyle(draggable, {opacity:""});
},
getSingleObjectDragGUI: function() {
return this.htmlElement;
},
getMultiObjectDragGUI: function( draggables ) {
return this.htmlElement;
},
getDroppedGUI: function() {
return this.htmlElement;
},
toString: function() {
return this.type + ":" + this.htmlElement + ":";
},
getParams: function() {
return this.params;
}
};
compDropzone = Class.create();
compDropzone.prototype = {
initialize: function( htmlElement , params, dropObjectAppendChild) {
this.htmlElement  = $(htmlElement);
this.absoluteRect = null;
this.params = params;
if(dropObjectAppendChild == null || dropObjectAppendChild == undefined)
this.dropObjectAppendChild = false;
else
this.dropObjectAppendChild = dropObjectAppendChild;
this.showingHover = false;
this.ChgSeqHover = null;
this.ChgSeqPosition = null;
},
setDropObjectAppendChild: function(dropObjectAppendChild) {
this.dropObjectAppendChild = dropObjectAppendChild;
},
getParams: function() {
return this.params;
},
getHTMLElement: function() {
return this.htmlElement;
},
clearPositionCache: function() {
this.absoluteRect = null;
},
getAbsoluteRect: function() {
if ( this.absoluteRect == null ) {
var htmlElement = this.getHTMLElement();
var offset = Position.cumulativeOffsetScroll(htmlElement);
Position.prepare();//this.deltaY,this.deltaX
var pos = {"x":offset[0],"y":offset[1]};
this.absoluteRect = {
top:    pos.y,
left:   pos.x,
bottom: pos.y + htmlElement.offsetHeight,
right:  pos.x + htmlElement.offsetWidth
};
}
return this.absoluteRect;
},
activate: function() {
var htmlElement = this.getHTMLElement();
if (htmlElement == null  || this.showingActive)
return;
this.showingActive = true;
},
deactivate: function() {
var htmlElement = this.getHTMLElement();
if (htmlElement == null || !this.showingActive)
return;
this.showingActive = false;
},
showHover: function(e) {
var htmlElement = this.getHTMLElement();
if ( this._showHover(htmlElement) )
return;
htmlElement.style.backgroundColor = "#ffff99";
},
_showHover: function(htmlElement) {
if ( htmlElement == null || this.showingHover )
return false;
this.saveHoverBackgroundColor = htmlElement.style.backgroundColor;
this.saveHoverBorderWidth = htmlElement.style.borderWidth;
this.saveHoverBorderStyle = htmlElement.style.borderStyle;
this.saveHoverBorderColor = htmlElement.style.borderColor;
this.showingHover = true;
return true;
},
hideHover: function(e) {
var htmlElement = this.getHTMLElement();
if ( this._hideHover(htmlElement) )
return;
},
_hideHover: function(htmlElement) {
if ( htmlElement == null || !this.showingHover )
return;
htmlElement.style.backgroundColor = this.saveHoverBackgroundColor;
if(this.saveHoverBorderWidth != "") htmlElement.style.borderWidth = this.saveHoverBorderWidth;
if(this.saveHoverBorderStyle != "") htmlElement.style.borderStyle = this.saveHoverBorderStyle;
if(this.saveHoverBorderColor != "") htmlElement.style.borderColor = this.saveHoverBorderColor;
this.showingHover = false;
},
showChgSeqHover: function(event, pos) {
var htmlElement = this.getHTMLElement();
var offset = Position.cumulativeOffset(htmlElement);
var ex = offset[0];
var ey = offset[1];
var center_y = ey + (htmlElement.offsetHeight/2);
var y = Event.pointerY(event);
if(this.ChgSeqHover == undefined || this.ChgSeqHover == null) {
this.ChgSeqHover = document.createElement("div");
document.body.appendChild(this.ChgSeqHover);
}
this.ChgSeqHover.style.width = htmlElement.offsetWidth + "px";
this.ChgSeqHover.style.height = "1px";
this.ChgSeqHover.style.position = "absolute";
this.ChgSeqHover.style.left = ex  + "px";
commonCls.max_zIndex = commonCls.max_zIndex + 1;
this.ChgSeqHover.style.zIndex = commonCls.max_zIndex;
if(pos != undefined) {
this.ChgSeqPosition = pos;
if(pos == "bottom") this.ChgSeqHover.style.top = (ey + htmlElement.offsetHeight)  + "px";
else this.ChgSeqHover.style.top = ey  + "px";
} else if(y > center_y) {
this.ChgSeqPosition = "bottom";
this.ChgSeqHover.style.top = (ey + htmlElement.offsetHeight)  + "px";
} else {
this.ChgSeqPosition = "top";
this.ChgSeqHover.style.top = ey  + "px";
}
this.ChgSeqHover.style.borderTop = "3px";
this.ChgSeqHover.style.borderTopStyle = "solid";
this.ChgSeqHover.style.borderTopColor = "#ffff00";
},
showChgSeqHoverInside: function(event) {
var htmlElement = this.getHTMLElement();
this.ChgSeqPosition = "inside";
htmlElement.style.backgroundColor = "#ffff99";
},
hideChgSeqHover: function(event) {
var htmlElement = this.getHTMLElement();
if ( this._hideHover(htmlElement) )
return;
if(this.ChgSeqHover) {
Element.remove(this.ChgSeqHover);
this.ChgSeqHover = null;
this.ChgSeqPosition = null;
}
},
acceptChgSeq: function(draggableObjects, dropElement, pos) {
var htmlElement = this.getHTMLElement();
if ( htmlElement == null )
return;
var n = draggableObjects.length;
for ( var i = 0 ; i < n ; i++ ) {
var theGUI = draggableObjects[i].getDroppedGUI();
if ( Element.getStyle( theGUI, "position" ) == "absolute" )
{
theGUI.style.position = "static";
theGUI.style.top = "";
theGUI.style.left = "";
}
if(dropElement) {
htmlElement = dropElement;
}
if(pos) this.ChgSeqPosition = pos;
if(this.ChgSeqPosition == "top") {
htmlElement.parentNode.insertBefore(theGUI, htmlElement);
} else if(this.ChgSeqPosition == "bottom"){
var next_el = htmlElement.nextSibling;
if(!next_el) {
if(htmlElement.parentNode.tagName.toLowerCase() == "table") {
var append_el = document.createElement("tbody");
htmlElement.parentNode.appendChild(append_el);
} else {
var append_el = htmlElement.parentNode;
}
append_el.appendChild(theGUI);
} else {
next_el.parentNode.insertBefore(theGUI, next_el);
}
} else {
var next_el = htmlElement.nextSibling;
if(next_el.tagName.toLowerCase() == "table") {
var append_el = document.createElement("tbody");
next_el.appendChild(append_el);
} else {
var append_el = next_el;
}
append_el.appendChild(theGUI);
}
commonCls.blockNotice(null, theGUI);
}
},
canAccept: function(draggableObjects) {
return true;
},
accept: function(draggableObjects) {
var htmlElement = this.getHTMLElement();
if ( htmlElement == null )
return;
if(this.dropObjectAppendChild) {
var n = draggableObjects.length;
for ( var i = 0 ; i < n ; i++ )
{
var theGUI = draggableObjects[i].getDroppedGUI();
if ( Element.getStyle( theGUI, "position" ) == "absolute" )
{
theGUI.style.position = "static";
theGUI.style.top = "";
theGUI.style.left = "";
}
htmlElement.appendChild(theGUI);
}
}
},
save: function(draggableObjects) {
return true;
}
};

var compLiveGrid = Class.create();
compLiveGrid.prototype = {
initialize: function(top_el, visibleRows, totalRows, action_name ,options ) {
if(visibleRows > totalRows) {
visibleRows = totalRows;
}
this.top_el = top_el;
this.action_name = action_name;
this.limit_str = "limit";
this.offset_str = "offset";
this.sort_col_str = "sort_col";
this.sort_dir_str = "sort_dir";
if(action_name != undefined && action_name != null) var prefetchBuffer = true;
else  var prefetchBuffer = false;
this.options = {
prefetchBuffer:       prefetchBuffer,
tableClass:           'grid',
scrollerBorderRight: '1px solid #ababab',
bufferTimeout:        20000,
sort:                 false,
sort_prefix:          "_sort_",
sortAscendImg:        _nc_core_base_url + '/images/comp/livegrid/sort_asc.gif',
sortDescendImg:       _nc_core_base_url + '/images/comp/livegrid/sort_desc.gif',
sortImageWidth:       9,
sortImageHeight:      5,
onSendCallback:       null,
onRefreshComplete:    null,
requestParameters:    null
};
Object.extend(this.options, options || {});
this.table = Element.getChildElementByClassName(this.top_el,this.options.tableClass);
this.addLiveGridHtml();
var rowCount = this.table.rows.length;
var columnCount  = (rowCount == 0) ? 0 : this.table.rows[0].cells.length;
this.metaData    = new compLiveGrid.LiveGridMetaData(visibleRows, totalRows, columnCount, this.options);
this.buffer      = new compLiveGrid.LiveGridBuffer(this.metaData, visibleRows, 0, rowCount, this.table);
for (var i=0; i < rowCount; i++) {
if(this.table.rows[i]) {
if ( !this.options.prefetchBuffer) {
this.buffer.rows[i]= true;
}
this.buffer.value_el_rows[i] = this.table.rows[i];
this.buffer.key_el_rows[this.table.rows[i]] = i;
this.buffer.initRow(this.table.rows[i]);
if(i > visibleRows - 1) {
Element.addClassName(this.table.rows[i],"display-none");
}
}
}
this.viewPort = new compLiveGrid.GridViewPort(this.table,
this.table.offsetHeight/visibleRows,
visibleRows,
this.buffer, this);
this.scroller    = new compLiveGrid.LiveGridScroller(this, this.viewPort);
this.options.sortHandler = this.sortHandler.bind(this);
this.table_header = Element.getChildElementByClassName(this.top_el,this.options.tableClass+'_header');
if ( this.table_header ) {
if(browser.isIE) {
this.table_header.style.width = this.table.offsetWidth + "px";
}
if(this.options.sort) {
this.sort = new compLiveGrid.LiveGridSort(this.options.tableClass+'_header', this.table_header ,this.options);
}
for (var i=0,row_len = this.table_header.rows.length; i < row_len; i++) {
this.buffer.initRow(this.table_header.rows[i]);
}
}
this.processingRequest = null;
if ( this.options.prefetchBuffer) {
var offset = 0;
if (this.options.offset ) {
offset = this.options.offset;
this.scroller.moveScroll(offset);
this.viewPort.scrollTo(this.scroller.rowToPixel(offset));
} else {
this.viewPort.refreshContents(offset);
}
if (this.options.sortCol) {
this.sortCol = options.sortCol;
this.sortDir = options.sortDir;
}
this.requestContentRefresh(offset);
}
if(browser.isIE) {
this.table.style.width = this.table.offsetWidth + "px";
}
},
addLiveGridHtml: function() {
if (this.table.getElementsByTagName("thead").length > 0){
var tableHeader = this.table.cloneNode(true);
tableHeader.setAttribute('class', this.options.tableClass+'_header');
for( var i = 0; i < tableHeader.tBodies.length; i++ )
tableHeader.removeChild(tableHeader.tBodies[i]);
this.table.deleteTHead();
this.table.parentNode.insertBefore(tableHeader,this.table);
}
new Insertion.Before(this.table, "<div class='"+this.options.tableClass+"_container'></div>");
this.table.previousSibling.appendChild(this.table);
new Insertion.Before(this.table,"<table border='0' cellspacing='0' cellpadding='0' class='"+this.options.tableClass+"_viewport'><tr><td><div></div></td><td class='valign-top'></td></tr></table>");
var table_view = Element.getChildElementByClassName(this.top_el,this.options.tableClass + "_viewport");
var div_el = Element.getChildElement(table_view.rows[0].cells[0]);
div_el.appendChild(this.table);
},
resetContents: function() {
this.scroller.moveScroll(0);
this.buffer.clear();
this.viewPort.clearContents();
},
sortHandler: function(column) {
if(!column) return ;
this.sortCol = column.name;
this.sortDir = column.currentSort;
this.resetContents();
this.requestContentRefresh(0);
},
adjustRowSize: function() {
},
setTotalRows: function( newTotalRows, reset_flag ) {
if(reset_flag) this.resetContents();
this.totalRows = newTotalRows;
if(this.visibleRows > this.totalRows) {
this.visibleRows = this.totalRows;
this.viewPort.visibleRows = this.visibleRows;
}
this.metaData.setTotalRows(newTotalRows);
this.scroller.updateSize();
},
handleTimedOut: function() {
this.processingRequest = null;
},
fetchBuffer: function(offset) {
if ( this.buffer.isInRange(offset) ) {
return;
}
if (this.processingRequest) {
setTimeout( function(){this.fetchBuffer(offset);}.bind(this), 300);
return;
}
var bufferStartPos = this.buffer.getFetchOffset(offset);
this.processingRequest = true;
var fetchSize = this.buffer.getFetchSize(bufferStartPos);
var partialLoaded = false;
var queryString;
if (this.options.requestParameters)
queryString = this._createQueryString(this.options.requestParameters, 0);
if(this.action_name == undefined) {
return;
}
queryString = (queryString == null) ? '' : '&'+queryString;
queryString  = queryString+'&' + this.limit_str + '='+fetchSize+'&' + this.offset_str + '='+bufferStartPos;
if (this.sortCol) {
queryString = queryString+'&' + this.sort_col_str + '='+escape(this.sortCol)+'&' + this.sort_dir_str + '='+this.sortDir;
}
var send_params = new Object();
send_params["method"] = "get";
send_params["param"] = this.action_name + queryString;
send_params["top_el"] = this.top_el;
send_params["eval_flag"] = 0;
send_params["callbackfunc"] = function(ajaxResponse){
try {
this.buffer.update(ajaxResponse,bufferStartPos);
}
catch(err) {}
finally {this.processingRequest = null;}
if(this.options.onSendCallback != null) {
this.options.onSendCallback(ajaxResponse, this.action_name + queryString);
}
}.bind(this);
commonCls.send(send_params);
},
setRequestParams: function() {
this.options.requestParameters = [];
for ( var i=0 ; i < arguments.length ; i++ )
this.options.requestParameters[i] = arguments[i];
},
requestContentRefresh: function(contentOffset) {
this.fetchBuffer(contentOffset);
},
_createQueryString: function( theArgs, offset ) {
var queryString = ""
if (!theArgs)
return queryString;
for ( var i = offset,theArgs_len = theArgs.length ; i < theArgs_len ; i++ ) {
if ( i != offset )
queryString += "&";
var anArg = theArgs[i];
if ( anArg.name != undefined && anArg.value != undefined ) {
queryString += anArg.name +  "=" + escape(anArg.value);
}
else {
var ePos  = anArg.indexOf('=');
var argName  = anArg.substring( 0, ePos );
var argValue = anArg.substring( ePos + 1 );
queryString += argName + "=" + escape(argValue);
}
}
return queryString;
}
};
compLiveGrid.LiveGridMetaData = Class.create();
compLiveGrid.LiveGridMetaData.prototype = {
initialize: function( pageSize, totalRows, columnCount, options ) {
this.pageSize  = pageSize;
this.totalRows = totalRows;
this.setOptions(options);
this.ArrowHeight = 16;
this.columnCount = columnCount;
},
setOptions: function(options) {
this.options = {
largeBufferSize    : 2.0,
nearLimitFactor    : 0.2
};
Object.extend(this.options, options || {});
},
getPageSize: function() {
return this.pageSize;
},
getTotalRows: function() {
return this.totalRows;
},
setTotalRows: function(n) {
this.totalRows = n;
},
getLargeBufferSize: function() {
return parseInt(this.options.largeBufferSize * this.pageSize);
},
getLimitTolerance: function() {
return parseInt(this.getLargeBufferSize() * this.options.nearLimitFactor);
}
};
compLiveGrid.LiveGridScroller = Class.create();
compLiveGrid.LiveGridScroller.prototype = {
initialize: function(liveGrid, viewPort) {
this.liveGrid = liveGrid;
this.viewPort = viewPort;
this.metaData = liveGrid.metaData;
this.createScrollBar();
this.scrollTimeout = null;
this.lastScrollPos = 0;
this.rows = new Array();
this.handleScrollEvent = null;
},
isUnPlugged: function() {
return this.scrollerDiv.onscroll == null;
},
plugin: function() {
Event.observe(this.scrollerDiv,"scroll", this.handleScroll.bindAsEventListener(this), false, this.liveGrid.top_el);
},
unplug: function() {
Event.stopObserving(this.scrollerDiv,"scroll", this.handleScroll.bindAsEventListener(this), false, this.liveGrid.top_el);
},
sizeIEHeaderHack: function() {
if ( !browser.isIE ) return;
var headerTable = this.liveGrid.table_header;
if ( headerTable )
headerTable.rows[0].cells[0].style.width =
(headerTable.rows[0].cells[0].offsetWidth + 1) + "px";
},
createScrollBar: function() {
var visibleHeight = this.liveGrid.viewPort.visibleHeight();
this.scrollerDiv  = document.createElement("div");
var scrollerStyle = this.scrollerDiv.style;
this.heightDiv = document.createElement("div");
if(this.viewPort.visibleRows != this.metaData.totalRows) {
scrollerStyle.borderRight = this.liveGrid.options.scrollerBorderRight;
scrollerStyle.width       = "19px";
if(browser.isIE) {
scrollerStyle.overflowY = "scroll";
}
this.heightDiv.style.width  = "1px";
} else {
scrollerStyle.width       = "0px";
}
scrollerStyle.height      = visibleHeight + "px";
scrollerStyle.overflow    = "auto";
var height_buf = parseInt(visibleHeight *
this.metaData.getTotalRows()/this.metaData.getPageSize());
if(!isNaN(height_buf) && height_buf != null) this.heightDiv.style.height = height_buf + "px" ;
this.plugin();
var table = this.liveGrid.table;
table.parentNode.parentNode.parentNode.cells[1].appendChild(this.scrollerDiv);
this.scrollerDiv.appendChild(this.heightDiv);
if(this.viewPort.visibleRows != this.metaData.totalRows) {
var eventName = browser.isIE ? "mousewheel" : "DOMMouseScroll";
Event.observe(table, eventName,
function(evt) {
if (evt.wheelDelta>=0 || evt.detail < 0)
this.scrollerDiv.scrollTop -= (2*this.viewPort.rowHeight);
else
this.scrollerDiv.scrollTop += (2*this.viewPort.rowHeight);
this.handleScroll(false);
}.bindAsEventListener(this),
false, this.liveGrid.top_el);
}
},
updateSize: function() {
if(this.viewPort.visibleRows == this.metaData.totalRows) {
this.scrollerDiv.style.width       = "0px";
}
this.scrollerDiv.style.height = (this.viewPort.rowHeight * this.viewPort.visibleRows) + "px";
var table = this.liveGrid.table;
var visibleHeight = this.viewPort.visibleHeight();
this.heightDiv.style.height = parseInt(visibleHeight *
this.metaData.getTotalRows()/this.metaData.getPageSize()) + "px";
},
rowToPixel: function(rowOffset) {
return (rowOffset / this.metaData.getTotalRows()) * this.heightDiv.offsetHeight
},
moveScroll: function(rowOffset) {
this.scrollerDiv.scrollTop = this.rowToPixel(rowOffset);
if ( this.metaData.options.onscroll )
this.metaData.options.onscroll( this.liveGrid, rowOffset );
},
handleScroll: function() {
if ( this.scrollTimeout ) {
clearTimeout( this.scrollTimeout );
}
if ( this.handleScrollEvent ) {
clearTimeout( this.handleScrollEvent );
}
this.handleScrollEvent = setTimeout(this.handleScrollTimer.bind(this), 0 );
},
handleScrollTimer: function() {
var scrollDiff = this.lastScrollPos-this.scrollerDiv.scrollTop;
if (scrollDiff != 0.00) {
var r = this.scrollerDiv.scrollTop % this.viewPort.rowHeight;
if (r != 0) {
this.unplug();
if ( scrollDiff < 0 ) {
this.scrollerDiv.scrollTop += (this.viewPort.rowHeight-r);
} else {
this.scrollerDiv.scrollTop -= r;
}
this.plugin();
}
}
var contentOffset = Math.round(this.scrollerDiv.scrollTop / this.viewPort.rowHeight);
this.liveGrid.requestContentRefresh(contentOffset);
this.viewPort.scrollTo(this.scrollerDiv.scrollTop);
if ( this.metaData.options.onscroll )
this.metaData.options.onscroll( this.liveGrid, contentOffset );
this.scrollTimeout = setTimeout(this.scrollIdle.bind(this), 1200 );
this.lastScrollPos = this.scrollerDiv.scrollTop;
},
scrollIdle: function() {
if ( this.metaData.options.onscrollidle )
this.metaData.options.onscrollidle();
}
};
compLiveGrid.LiveGridBuffer = Class.create();
compLiveGrid.LiveGridBuffer.prototype = {
initialize: function(metaData, visibleRows, startPos, size, table) {
this.startPos = 0;
this.size     = size;
this.metaData = metaData;
this.rows         = new Array();
this.value_el_rows= new Array();
this.key_el_rows  = new Array();
this.visibleRows = visibleRows;
this.width_buf = new Object();
this.table = table;
this.maxFetchSize = metaData.getLargeBufferSize();
if(this.table.rows[0]) {
this.clone_row = this.table.rows[0].cloneNode(true);
var class_name = this.clone_row.className;
if(class_name) {
var class_arr = class_name.split(/\s+/);
var add_class_arr = new Array();
class_arr.each(function(name) {
if(name.match(/^grid_.*/)) {
this.push(name);
}
}.bind(add_class_arr));
this.clone_row.className = add_class_arr.join(' ');
}
this.initRow(this.clone_row, true);
}
},
update: function(ajaxResponse, start) {
var tbody = Element.getChildElement(this.table);
if(tbody == null || tbody.tagName.toLowerCase() != "tbody") {
tbody = this.table;
}
if(typeof ajaxResponse == 'string') {
if(ajaxResponse != "") {alert(ajaxResponse);}
return false;
} else {
var lists = Element.getChildElement(ajaxResponse);
var child_len=lists.childNodes.length;
for (var i = 0; i < child_len; i++) {
var table_row_el = this.value_el_rows[start+i];
var row_el = lists.childNodes[i];
var class_name = Element.readAttribute(row_el, "class");
if(class_name) {
Element.addClassName(table_row_el, class_name);
}
var id = Element.readAttribute(row_el, "id");
if(id) {
table_row_el.id = id;
}
if(row_el) {
for (var j = 0,cell_len=row_el.childNodes.length; j < cell_len; j++) {
var cell_el = row_el.childNodes[j];
var class_name = Element.readAttribute(cell_el, "class");
if(class_name) {
Element.addClassName(table_row_el.childNodes[j], class_name);
}
var text = "";
if(cell_el.firstChild != null) {text = cell_el.firstChild.nodeValue;}
Element.getChildElement(table_row_el.childNodes[j]).innerHTML = cell_el.textContent || cell_el.text || text;
}
this.rows[start+i] = true;
}
}
}
this.size = start + child_len;
},
clear: function() {
this.rows = new Array();
this.startPos =  0;
this.size = 0;
},
isInRange: function(position) {
var end_position = position + this.metaData.getPageSize() - 1;
if(end_position > this.metaData.getTotalRows() - 1) {
end_position = this.metaData.getTotalRows() - 1;
}
var now_pos = position;
while (this.rows[now_pos]) {
if(now_pos == end_position) return true;
now_pos++;
}
return false;
},
isNearingTopLimit: function(position) {
return position - this.startPos < this.metaData.getLimitTolerance();
},
endPos: function() {
return this.table.rows.length;
},
isNearingBottomLimit: function(position) {
return this.endPos() - (position + this.metaData.getPageSize()) < this.metaData.getLimitTolerance();
},
isAtTop: function() {
return this.startPos == 0;
},
isAtBottom: function() {
return this.endPos() == this.metaData.getTotalRows();
},
isNearingLimit: function(position) {
return ( !this.isAtTop()    && this.isNearingTopLimit(position)) ||
( !this.isAtBottom() && this.isNearingBottomLimit(position) );
},
getFetchSize: function(offset) {
var adjustedOffset = offset;
var adjustedSize = 0;
var endFetchOffset = this.maxFetchSize  + adjustedOffset;
if (endFetchOffset > this.metaData.totalRows) {
if(this.metaData.totalRows > 0) {
endFetchOffset = this.metaData.totalRows;
}
}
if(this.rows[endFetchOffset]) {
var row_num = endFetchOffset - 1;
while (this.rows[row_num]) {
row_num--;
if(row_num < adjustedOffset) {
return 1;
}
}
endFetchOffset = row_num + 1;
}
adjustedSize = endFetchOffset - adjustedOffset;
return adjustedSize;
},
getFetchOffset: function(offset) {
var adjustedOffset = offset;
if(this.rows[adjustedOffset]) {
var row_num = adjustedOffset + 1;
while (this.rows[row_num]) {
row_num++;
if(row_num > this.metaData.getTotalRows() - 1) {
return offset;
}
}
adjustedOffset = row_num;
}
return adjustedOffset;
},
convertSpaces: function(s) {
return s.split(" ").join("&nbsp;");
},
initRow: function(htmlRow, clone_flag) {
for (var j=0,row_len=htmlRow.childNodes.length; j < row_len; j++) {
var child_el = Element.getChildElement(htmlRow.childNodes[j]);
if(!child_el || child_el.tagName.toLowerCase() != 'div' || Element.getStyle( child_el, "overflow" ) != "hidden") {
if((browser.isIE || browser.isSafari) && clone_flag) {
this.appendDivElement(htmlRow.childNodes[j], this.table.rows[0].childNodes[j]);
} else {
this.appendDivElement(htmlRow.childNodes[j]);
}
child_el = Element.getChildElement(htmlRow.childNodes[j]);
}
if(child_el && clone_flag) {
child_el.innerHTML = "";
}
}
},
appendDivElement: function(el, first_el) {
if(el.className != "") {
if(this.width_buf[el.className]) {
var width = this.width_buf[el.className];
} else {
var width = valueParseInt(Element.getStyle( el, "width" ));
this.width_buf[el.className] = width;
}
} else {
var width = valueParseInt(Element.getStyle( el, "width" ));
}
if(width == 0 && first_el) {
var width = valueParseInt(Element.getStyle( first_el, "width" ));
this.width_buf[el.className] = width;
}
var child_el  = document.createElement("div");
var children_length = el.childNodes.length;
var el_arr = new Object;
for (var k = 0; k < children_length; k++) {
var child = el.childNodes[k];
if (child.nodeType == 1) {
el_arr[k] = child;
} else if(child.nodeType == 3) {
el_arr[k] = document.createTextNode(child.nodeValue);
child.nodeValue = "";
}
}
el.appendChild(child_el);
for (var k = 0; k < children_length; k++) {
child_el.appendChild(el_arr[k]);
}
this.divAddStyle(child_el, el, width);
},
divAddStyle: function(el, parent_el, parent_width) {
var width = parent_width;
el.style.width = width + "px";
el.style.overflow = "hidden";
el.style.whiteSpace = "nowrap";
}
};
compLiveGrid.GridViewPort = Class.create();
compLiveGrid.GridViewPort.prototype = {
initialize: function(table, rowHeight, visibleRows, buffer, liveGrid) {
rowHeight = (isNaN(rowHeight) || rowHeight == null || rowHeight == undefined) ? 0 : rowHeight;
this.lastDisplayedStartPos = 0;
this.div = table.parentNode;
this.table = table
this.rowHeight = rowHeight;
this.div.style.whiteSpace = "nowrap";
this.buffer = buffer;
this.liveGrid = liveGrid;
this.visibleRows = visibleRows;
this.lastPixelOffset = 0;
this.startPos = 0;
this.lastStartPos = 0;
if(liveGrid.options.prefetchBuffer) {
this.isBlank = true;
} else {
this.isBlank = false;
}
},
bufferChanged: function() {
var offset = Math.round(this.lastPixelOffset / this.rowHeight);
if(offset +  this.visibleRows > this.liveGrid.metaData.getTotalRows()) {
offset = this.liveGrid.metaData.getTotalRows() - this.visibleRows;
}
this.refreshContents(offset);
},
clearRows: function() {
for (var i = 0,child_len = this.liveGrid.table.rows.length; i < child_len; i++) {
var row_el = this.liveGrid.table.rows[i];
if(row_el) {
if(Element.hasClassName(row_el,"display-none")) {
row_el.className = this.liveGrid.buffer.clone_row.className + " display-none";
} else {
row_el.className = this.liveGrid.buffer.clone_row.className;
}
for (var j = 0,cell_len=row_el.childNodes.length; j < cell_len; j++) {
var cell_el = row_el.childNodes[j];
var grid_cell_el = this.liveGrid.buffer.clone_row.childNodes[j];
cell_el.className = grid_cell_el.className;
var child_el = Element.getChildElement(cell_el)
if(child_el) child_el.innerHTML = "";
}
}
}
this.liveGrid.buffer.rows = new Array();
},
clearContents: function() {
this.clearRows();
this.isBlank = true;
this.scrollTo(0);
this.startPos = 0;
this.lastStartPos = 0;
},
refreshContents: function(startPos) {
if (startPos == this.lastStartPos && !this.isPartialBlank && !this.isBlank) {
return;
}
var contentStartPos = startPos;
var contentEndPos = startPos + this.visibleRows;
var rowSize = contentEndPos - contentStartPos;
var blankSize = this.visibleRows - rowSize;
var lastRowPosLen = this.lastStartPos + this.visibleRows;
for (var i=this.lastStartPos; i < lastRowPosLen; i++) {
Element.addClassName(this.buffer.value_el_rows[i],"display-none");
}
var fetchSize = this.buffer.maxFetchSize;
var lastRowPosLen = startPos + fetchSize;
if(lastRowPosLen > this.liveGrid.metaData.getTotalRows()) {
lastRowPosLen = this.liveGrid.metaData.getTotalRows();
}
var insert_row = -1;
for (var i=startPos; i < lastRowPosLen; i++) {
var insert_flag = false;
if(this.buffer.value_el_rows[i]) {
var table_row_el = this.buffer.value_el_rows[i];
} else {
if(this.table.rows[i]) {
if(this.isBlank == false && this.buffer.key_el_rows[this.table.rows[i]] != i) {
insert_flag = true;
} else {
var table_row_el = this.table.rows[i];
}
} else {
insert_flag = true;
}
if(insert_flag) {
if(insert_row == -1) {
var rows_len = this.table.rows.length;
if(rows_len < i) {
var insert_row = rows_len;
} else {
var insert_row = i;
}
while (this.buffer.key_el_rows[this.table.rows[insert_row - 1]] > i) {
insert_row--;
if(insert_row == 0) {
break;
}
}
}
var table_row_el = this.table.insertRow(insert_row);
insert_row++;
for (var j = 0,cells_len = this.buffer.clone_row.childNodes.length; j < cells_len; j++) {
var insert_td = table_row_el.insertCell(j);
insert_td.className = this.buffer.clone_row.childNodes[j].className;
insert_td.innerHTML = this.buffer.clone_row.childNodes[j].innerHTML;
}
}
this.buffer.value_el_rows[i] = table_row_el;
this.buffer.key_el_rows[table_row_el] = i;
}
if(table_row_el.className == "") {
table_row_el.className = this.buffer.clone_row.className;
}
if(i < startPos + this.visibleRows) {
Element.removeClassName(table_row_el,"display-none");
} else {
Element.addClassName(table_row_el,"display-none");
}
}
this.isBlank = false;
this.isPartialBlank = blankSize > 0;
this.lastStartPos = startPos;
Element.addClassName(this.liveGrid.table,this.liveGrid.options.tableClass);
var onRefreshComplete = this.liveGrid.options.onRefreshComplete;
if (onRefreshComplete != null)
onRefreshComplete();
},
scrollTo: function(pixelOffset) {
if (this.lastPixelOffset == pixelOffset)
return;
var offset = Math.round(pixelOffset / this.rowHeight);
if(offset +  this.visibleRows > this.liveGrid.metaData.getTotalRows()) {
offset = this.liveGrid.metaData.getTotalRows() - this.visibleRows;
}
this.refreshContents(offset);
this.div.scrollTop = pixelOffset % this.rowHeight;
this.lastPixelOffset = pixelOffset;
},
visibleHeight: function() {
return parseInt(Element.getStyle(this.div, 'height'));
}
};
compLiveGrid.LiveGridSort = Class.create();
compLiveGrid.LiveGridSort.prototype = {
initialize: function(headerTableId, headerTable, options) {
this.headerTableId = headerTableId;
this.headerTable   = headerTable;
this.options = options;
this.setOptions();
this.applySortBehavior();
if ( this.options.sortCol ) {
this.setSortUI( this.options.sortCol, this.options.sortDir );
}
},
setSortUI: function( columnName, sortDirection ) {
var cols = this.options.columns;
for ( var i = 0 ; i < cols.length ; i++ ) {
if ( cols[i].name == columnName ) {
this.setColumnSort(i, sortDirection);
break;
}
}
},
setOptions: function() {
new Image().src = this.options.sortAscendImg;
new Image().src = this.options.sortDescendImg;
this.sort = this.options.sortHandler;
if ( !this.options.columns )
this.options.columns = this.introspectForColumnInfo();
else {
this.options.columns = this.convertToTableColumns(this.options.columns);
}
},
applySortBehavior: function() {
var tdList = this.headerTable.getElementsByTagName("th");
if(tdList.length == 0) {
tdList = this.headerTable.getElementsByTagName("td");
}
for (var i = 0,tdLen = tdList.length; i < tdLen; i++){
this.addSortBehaviorToColumn( i, tdList[i] );
}
},
addSortBehaviorToColumn: function( n, cell ) {
if ( this.options.columns[n].isSortable() ) {
cell.id            = this.headerTableId + '_' + n;
cell.style.cursor  = 'pointer';
cell.onclick       = this.headerCellClicked.bindAsEventListener(this);
Element.addClassName(cell, "grid_sort");
var child_el = Element.getChildElement(cell);
if(child_el) {
child_el.innerHTML     = child_el.innerHTML + '<span class="' + this.headerTableId + '_img_' + n + '">'
+ '&nbsp;&nbsp;&nbsp;</span>';
} else {
cell.innerHTML     = cell.innerHTML + '<span class="' + this.headerTableId + '_img_' + n + '">'
+ '&nbsp;&nbsp;&nbsp;</span>';
}
}
},
headerCellClicked: function(evt) {
var eventTarget = evt.target ? evt.target : evt.srcElement;
while (eventTarget.tagName.toLowerCase() != "td" && eventTarget.tagName.toLowerCase() != "th") {
eventTarget = eventTarget.parentNode;
}
var cellId = eventTarget.id;
var columnNumber = parseInt(cellId.substring( cellId.lastIndexOf('_') + 1 ));
var sortedColumnIndex = this.getSortedColumnIndex();
if ( sortedColumnIndex != -1 ) {
if ( sortedColumnIndex != columnNumber ) {
this.removeColumnSort(sortedColumnIndex);
this.setColumnSort(columnNumber, compLiveGrid.TableColumn.SORT_ASC);
} else {
this.toggleColumnSort(sortedColumnIndex);
}
} else {
this.setColumnSort(columnNumber, compLiveGrid.TableColumn.SORT_ASC);
}
if (this.options.sortHandler) {
this.options.sortHandler(this.options.columns[columnNumber]);
}
},
removeColumnSort: function(n) {
this.options.columns[n].setUnsorted();
this.setSortImage(n);
},
setColumnSort: function(n, direction) {
if(isNaN(n)) return ;
this.options.columns[n].setSorted(direction);
this.setSortImage(n);
},
toggleColumnSort: function(n) {
this.options.columns[n].toggleSort();
this.setSortImage(n);
},
setSortImage: function(n) {
var sortDirection = this.options.columns[n].getSortDirection();
var sortImageSpan = Element.getChildElementByClassName(this.headerTable, this.headerTableId + '_img_' + n);
if ( sortDirection == compLiveGrid.TableColumn.UNSORTED )
sortImageSpan.innerHTML = '&nbsp;&nbsp;';
else if ( sortDirection == compLiveGrid.TableColumn.SORT_ASC )
sortImageSpan.innerHTML = '&nbsp;&nbsp;<img width="'  + this.options.sortImageWidth    + '" ' +
'height="'+ this.options.sortImageHeight   + '" ' +
'src="'   + this.options.sortAscendImg + '"/>';
else if ( sortDirection == compLiveGrid.TableColumn.SORT_DESC )
sortImageSpan.innerHTML = '&nbsp;&nbsp;<img width="'  + this.options.sortImageWidth    + '" ' +
'height="'+ this.options.sortImageHeight   + '" ' +
'src="'   + this.options.sortDescendImg + '"/>';
},
getSortedColumnIndex: function() {
var cols = this.options.columns;
for ( var i = 0 ; i < cols.length ; i++ ) {
if ( cols[i].isSorted() )
return i;
}
return -1;
},
introspectForColumnInfo: function() {
var columns = new Array();
var tdList = this.headerTable.getElementsByTagName("th");
if(tdList.length == 0) {
tdList = this.headerTable.getElementsByTagName("td");
}
for (var i = 0,tdLen = tdList.length; i < tdLen; i++){
cellContent = this.deriveColumnNameFromCell(tdList[i]);
if(cellContent) {
columns.push( new compLiveGrid.TableColumn( cellContent, true ) );
} else {
columns.push( new compLiveGrid.TableColumn( cellContent, false ) );
}
}
return columns;
},
convertToTableColumns: function(cols) {
var columns = new Array();
for ( var i = 0 ; i < cols.length ; i++ )
columns.push( new compLiveGrid.TableColumn( cols[i][0], cols[i][1] ) );
return columns;
},
deriveColumnNameFromCell: function(cell) {
var className = cell.className.split(" ")[0];
var re_cut = new RegExp("^" + this.options.sort_prefix, "i");
if(className.match(re_cut)) {
var name = className.replace(re_cut,"");
if(name != "") {
return name.toLowerCase();
}
}
return null;
}
};
compLiveGrid.TableColumn = Class.create();
compLiveGrid.TableColumn.UNSORTED  = 0;
compLiveGrid.TableColumn.SORT_ASC  = "ASC";
compLiveGrid.TableColumn.SORT_DESC = "DESC";
compLiveGrid.TableColumn.prototype = {
initialize: function(name, sortable) {
this.name        = name;
this.sortable    = sortable;
this.currentSort = compLiveGrid.TableColumn.UNSORTED;
},
isSortable: function() {
return this.sortable;
},
isSorted: function() {
return this.currentSort != compLiveGrid.TableColumn.UNSORTED;
},
getSortDirection: function() {
return this.currentSort;
},
toggleSort: function() {
if ( this.currentSort == compLiveGrid.TableColumn.UNSORTED || this.currentSort == compLiveGrid.TableColumn.SORT_DESC )
this.currentSort = compLiveGrid.TableColumn.SORT_ASC;
else if ( this.currentSort == compLiveGrid.TableColumn.SORT_ASC )
this.currentSort = compLiveGrid.TableColumn.SORT_DESC;
},
setUnsorted: function(direction) {
this.setSorted(compLiveGrid.TableColumn.UNSORTED);
},
setSorted: function(direction) {
this.currentSort = direction;
}
};
var clsPages = Class.create();
clsPages.prototype = {
initialize: function() {
this.center_page_id = null;
this.pages_token = new Object();
this.move_el = null;
this.move_td = null;
this.xOffset = 0;
this.yOffset = 0;
this.start_x = 0;
this.start_y = 0;
this.move_column_el = null;
this.insert_tr = null;
this.insert_el = null;
this.insert_td = null;
this.active = null;
this.active_style = null;
this.insertAction = null;
this.insertMoveEndRowThreadNum = null;
this.insertMoveEndRowParentId = null;
this.inMoveDrag = false;
this.inChgBlockName = new Object();
this.inMoveShowHeader = new Object();
this.inCancelGroupingDrag = false;
this.insertMoveCellIndex = null;
this.insertMoveRowIndex = null;
this.insertMoveRowLength = null;
this.insertMoveCellLength = null;
this.insertMoveRowParentId = null;
this.insertMoveRowBlockId = null;
this.show_count = new Object();
this.winMoveDragStartEvent = new Object();
this.winMoveDragGoEvent = new Object();
this.winMoveDragStopEvent = new Object();
this.winGroupingEvent  = new Object();
this.parentThreadNum = 0;
this.parentParentid = 0;
this.cloneTopPos = new Object();
this.cloneColumnPos = Array();
this.clonePos = Array();
this.groupingList = Array();
this.block_left_padding = 8;
this.block_right_padding = 8;
this.block_top_padding = 8;
this.block_bottom_padding = 8;
},
pageInit: function(active_center) {
Event.observe(document,"mousedown",this.winMouseDownEvent.bindAsEventListener(this),false);
if($("_settingmode")) {
var centercolumn_el = $("__centercolumn");
if(active_center == 0 && _nc_layoutmode != "on" && centercolumn_el) {
var cell_el = Element.getChildElementByClassName(centercolumn_el,"cell");
if(!cell_el) {
var centercolumn_inf_mes_el = $("centercolumn_inf_mes");
if(centercolumn_inf_mes_el) {
var div = document.createElement("DIV");
div.innerHTML = pagesLang.centercolumnNoexists;
centercolumn_inf_mes_el.appendChild(div);
} else {
centercolumn_el.innerHTML = "<div id='centercolumn_inf_mes'><div>"+pagesLang.centercolumnNoexists+"</div></div>";
}
}
}
}
},
winMouseDownEvent: function(event) {
var el = Event.element(event);
if (Element.hasClassName(el,"header_btn") || Element.hasClassName(el,"header_btn_left")){
return false;
}
var divList = document.getElementsByTagName("div");
var check_flag = true;
for (var i = 0; i < divList.length; i++){
if (Element.hasClassName(divList[i],"cell")){
var child_el = Element.getChildElement(divList[i]);
if(child_el && Position.within(child_el, Event.pointerX(event),Event.pointerY(event))) {
check_flag = false;
break;
}
}
}
if(check_flag) {
var divList = document.getElementsByTagName("div");
for (var i = 0; i < divList.length; i++){
if (Element.hasClassName(divList[i],"cell")){
pagesCls.cancelSelectStyle(divList[i]);
}
}
}
},
setToken: function(page_id,token_value,center_flag) {
if(center_flag)
this.center_page_id = page_id;
this.pages_token[page_id] = token_value;
},
setShowCount: function(page_id,show_count) {
this.show_count[page_id] = show_count;
},
blockChangeName: function(event) {
var block_el = Element.getParentElementByClassName(this,"module_box");
if(Element.hasClassName(this,"nc_block_title") || Element.hasClassName(this,"_block_title_abs")) {
var title_el = this;
if(Element.getChildElement(title_el) && Element.getChildElement(title_el).tagName == "INPUT") {
} else {
var text = title_el.innerHTML.trim();
title_el.innerHTML = "<input style='width:90%;' name=\"title_el\" type=\"text\" maxlength=\"50\" title=\"" + text + "\" value=\"" + text + "\" onblur=\"pagesCls.blockChangeNameCommit(event,this);\" onkeypress=\"if (event.keyCode == 13) {if(browser.isNS && !browser.isSafari) {pagesCls.blockChangeNameCommit(event,this);}else{this.blur();}return false;}\" autocomplete=\"off\" />";
}
commonCls.focus(title_el);
pagesCls.inChgBlockName[block_el.id] = true;
}
},
blockChangeNameCommit: function(event,input_el) {
var event_el = Event.element(event);
var block_el = Element.getParentElementByClassName(event_el,"module_box");
var title_el = new Array();
title_el[0] = Element.getChildElementByClassName(block_el,"nc_block_title");
title_el[2] = Element.getChildElementByClassName(block_el,"_block_title_event");
if(!title_el[2]) {title_el[2] = title_el[0];}
if(block_el) {
title_el[1] = Element.getChildElementByClassName(block_el,"_block_title_abs");
var text = input_el.value.escapeHTML();
var parent_el = input_el.parentNode;
if(!parent_el)
return false;
var old_text = input_el.title.escapeHTML();
parent_el.innerHTML = text;
var queryParams = commonCls.getParams(block_el);
var page_id = queryParams["page_id"];
var params = new Object();
params["method"] = "post";
params["param"] = "pages_actionblock_chgblockname" + "&block_name=" + encodeURIComponent(input_el.value);
params["top_el"] = block_el;
params["loading_el"] = parent_el;
params["callbackfunc_error"] = function(old_text,res){commonCls.alert(res); this.innerHTML = old_text;}.bind(parent_el);
params["func_error_param"] = old_text;
params["callbackfunc"] = function(text){
this[0].innerHTML = text;
if( this[1] ) this[1].innerHTML = text;
if(text == '' && !Element.hasClassName(this[0], "display-none")) {
if(_nc_layoutmode=="off") Element.addClassName(this[2],"display-none");
} else {
Element.removeClassName(this[2],"display-none");
}
pagesCls.inChgBlockName[block_el.id] = false;
}.bind(title_el);
params["func_param"] = text;
params["token"] = pagesCls.pages_token[page_id];
commonCls.send(params);
}
return false;
},
addBlock: function(event, page_id, module_id_arr) {
var module_id_arr_list = module_id_arr.split("_");
var module_id = module_id_arr_list[0];
var dir_name = module_id_arr_list[1];
if(module_id == "0") return;
if(dir_name != "login") {
var scripts = document.getElementsByTagName("script");
for (var i = 0,scripts_len = scripts.length; i < scripts_len; i++) {
var script_el = scripts[i];
if(script_el.src.match("common_download_js")) {
var queryParams = script_el.src.parseQuery();
var re_dir_name = new RegExp("(.*?)(common_download_js&dir_name=)(.*?)("+dir_name+")(.*)", "i");
if(	!script_el.src.match(re_dir_name)) {
commonCls.addScript(_nc_core_base_url + _nc_index_file_name + "?action=common_download_js&dir_name=" + dir_name + "&add_block_flag=1"+"&vs=" + _nc_js_vs);
}
break;
}
}
}
var event_el = Event.element(event);
var addmobule_box_el = Element.getParentElementByClassName(event_el,"addmobule_box");
var main_column_el = Element.getChildElementByClassName(addmobule_box_el.parentNode.nextSibling, "main_column");
var addblock_params = new Object();
var postBody = "pages_actionblock_addblock" + "&module_id=" + module_id + "&page_id=" + page_id +
"&topmargin=" + this.block_top_padding + "&rightmargin=" + this.block_right_padding +
"&bottommargin=" +this.block_bottom_padding  + "&leftmargin=" + this.block_left_padding +
"&_show_count=" + pagesCls.show_count[page_id];
addblock_params["method"] = "post";
addblock_params["param"] = postBody;
addblock_params["loading_el"] = event_el;
addblock_params["callbackfunc"] = function(res){
for (var key in pagesCls.inMoveShowHeader) {
if(pagesCls.inMoveShowHeader[key]) {
var header_el = $("_move_header" + key);
Element.addClassName(header_el, "display-none");
pagesCls.inMoveShowHeader[key] = false;
}
}
var table_el = Element.getChildElement(main_column_el);
var tr = table_el.getElementsByTagName("tr")[0];
var column_el = Element.getChildElement(tr);
if(!column_el) {
column_el = tr.insertCell(0);
column_el.className = "column valign-top";
}
var div = document.createElement("DIV");
div.className = "cell";
div.style.padding = this.block_top_padding + "px" + " " + this.block_right_padding + "px" + " " + this.block_bottom_padding + "px" + " " + this.block_left_padding + "px";
var child_el = Element.getChildElement(column_el);
if(child_el) {
column_el.insertBefore(div, child_el);
} else {
column_el.appendChild(div);
}
var queryParams = res.parseQuery();
commonCls.addBlockTheme(queryParams['theme_name']);
queryParams['page_id'] = page_id;
queryParams['module_id'] = module_id;
queryParams['_layoutmode'] = _nc_layoutmode;
var add_params = new Object();
var action_name_list = queryParams['action'].split("_");
if(action_name_list[1] && action_name_list[1]=="action") {
add_params["method"] = "post";
}
add_params["param"] = queryParams;
add_params["target_el"] = div;
add_params["callbackfunc"] = function(res){
if(browser.isGecko) {
}
this.show_count[page_id]++;
}.bind(this);
add_params["callbackfunc_error"] = function(res){this.show_count[page_id]++; commonCls.alert(res); location.reload();}.bind(this);
commonCls.send(add_params);
}.bind(this);
addblock_params["callbackfunc_error"] = function(res){commonCls.alert(res); location.reload();};
addblock_params["token"] = pagesCls.pages_token[page_id];
commonCls.send(addblock_params);
event_el.selectedIndex = 0;
if(browser.isIE) {
document.body.focus();
} else {
event_el.blur();
}
},
deleteBlock: function(event, id, confirm_mes, request_flag) {
var target_el;
if (id == undefined) {
target_el = Event.element(event);
} else {
target_el = $(id);
}
var cell_el = Element.getParentElementByClassName(target_el,"cell");
if(cell_el) {
var td_el = Element.getParentElement(cell_el);
var tr_el = Element.getParentElement(td_el);
var top_el = Element.getChildElement(cell_el);
var title_el = Element.getChildElementByClassName(top_el,"nc_block_title");
var parent_cell = Element.getParentElementByClassName(tr_el,"cell");
if(parent_cell) {
parent_id = commonCls.getBlockid(parent_cell);
} else {
parent_id = 0;
}
} else {
var top_el = Element.getChildElement(document.body);
var title_el = Element.getChildElementByClassName(top_el,"nc_block_title");
}
if(confirm_mes != undefined || confirm_mes != null) {
var text = title_el.innerHTML.trim();
if(text == "") text = pagesLang.emptyBlockname;
if (!commonCls.confirm(confirm_mes.replace("%s", text))) return false;
}
var queryParams = commonCls.getParams(top_el);
var page_id = queryParams["page_id"];
if(cell_el) {
var count = 0;
for (var i = 0; i < td_el.childNodes.length; i++) {
var div = td_el.childNodes[i];
if(div && div.tagName == "DIV" && Element.hasClassName(div,"cell")) {
count++;
}
}
if(count == 1) {
Element.remove(td_el);
} else {
Element.remove(cell_el);
}
var cell_length = tr_el.cells.length;
if(cell_length == 0 && parent_id != 0) {
pagesCls.delEmptyBlock(parent_id);
}
} else {
Element.remove(top_el);
}
var delblock_params = new Object();
delblock_params["method"] = "post";
delblock_params["param"] = "pages_actionblock_deleteblock" +
"&_show_count=" + pagesCls.show_count[page_id];
delblock_params["top_el"] = top_el;
pagesCls.show_count[page_id]++;
if(request_flag == false) return;
if(cell_el) {
delblock_params["callbackfunc_error"] = function(res){commonCls.alert(res);location.reload();};
}
delblock_params["token"] = pagesCls.pages_token[page_id];
commonCls.send(delblock_params);
},
winMoveShowHeader: function(event, el, resize_flag) {
resize_flag = (resize_flag == undefined) ? false : resize_flag;
if(event) {
var el = $(this.id);
}
if(typeof pagesCls == 'undefined' || !el || (pagesCls.inMoveDrag || pagesCls.inMoveShowHeader["_move_header" + el.id]))
return false;
var _move_header = $("_move_header" + el.id);
if(_move_header) {
if(_move_header.style.position != "absolute") {
_move_header.style.position = "absolute";
if(Element.hasClassName(_move_header, "_move_header")) {
var _block_title = Element.getChildElementByClassName(_move_header, "_block_title_abs");
var _block_title_event = Element.getChildElementByClassName(_move_header,"_block_title_event_abs");
if(!_block_title_event) _block_title_event = _block_title;
var move_bar = Element.getChildElementByClassName(_move_header,"_move_bar");
Event.observe(_block_title, "mouseover", commonCls.blockNotice, false, el);
Event.observe(_block_title_event,"dblclick",pagesCls.blockChangeName.bindAsEventListener(_block_title),false, el);
pagesCls.winMoveDragStartEvent[el.id] = pagesCls.winMoveDragStart.bindAsEventListener(el);
Event.observe(move_bar,"mousedown",pagesCls.winMoveDragStartEvent[el.id],false, el);
pagesCls.winGroupingEvent[el.id] = pagesCls.onGroupingEvent.bindAsEventListener(el);
Event.observe(move_bar,"click",pagesCls.winGroupingEvent[el.id],false, el);
}
}
var offset = Position.cumulativeOffset(el);
var x = offset[0];
var y = offset[1];
var x2 = offset[0] + el.offsetWidth;
_move_header.style.left = x +"px";
_move_header.style.width = (x2 - x) +"px";
if(!resize_flag) {
Element.addClassName(_move_header, "visible-hide");
Element.removeClassName(_move_header, "display-none");
}
if(y < 0)  _move_header.style.top = "0px";
else _move_header.style.top = y +"px";
if(!resize_flag) {
Element.removeClassName(_move_header, "visible-hide");
pagesCls.inMoveShowHeader[el.id] = true;
commonCls.moveVisibleHide(_move_header);
if(event)Event.stop(event);
}
}
},
winMoveResizeHeader: function() {
if(_nc_layoutmode == "on" && typeof pagesCls != 'undefined') {
for (var key in pagesCls.inMoveShowHeader) {
if(pagesCls.inMoveShowHeader[key]) {
var header_el = $(key);
pagesCls.winMoveShowHeader(null, header_el, true);
}
}
}
},
winMoveHideHeader: function(event) {
var el = $(this.id);
var _move_header = $("_move_header" + this.id);
if(_move_header) {
if( Position.within(_move_header, Event.pointerX(event), Event.pointerY(event), 2) ) {
return;
}
if( !Position.within( el, Event.pointerX(event), Event.pointerY(event), 2) ) {
Element.addClassName(_move_header, "display-none");
pagesCls.inMoveShowHeader["_move_header" + this.id] = null;
commonCls.moveVisibleHide(_move_header);
Event.stop(event);
}
}
},
winMoveDragStart: function(event) {
if(!pagesCls || pagesCls.inMoveDrag)
return false;
var el = this.parentNode;
if(el.tagName == "BODY" || Element.hasClassName(el,"enlarged_display"))
return false;
if(pagesCls.inChgBlockName[this.id] == true ) {
return false;
}
pagesCls.move_column_el = Element.getParentElementByClassName(el,"main_column")
if(!pagesCls.move_column_el) {
pagesCls.move_column_el = Element.getChildElement(document.body);
pagesCls.parentThreadNum = $("_grouping_thread_num").value;
pagesCls.parentParentid = $("_grouping_parent_id").value;
pagesCls.insert_tr = Element.getParentElement(Element.getChildElementByClassName(pagesCls.move_column_el,"column"));
} else {
pagesCls.insert_tr = Element.getChildElement(pagesCls.move_column_el,3);
pagesCls.parentParentid = 0;
}
pagesCls.move_el = el;
pagesCls.move_td = Element.getParentElement(el);
var paddingLeft = valueParseInt(pagesCls.move_el.style.paddingLeft);
var paddingRight = valueParseInt(pagesCls.move_el.style.paddingRight);
var paddingTop = valueParseInt(pagesCls.move_el.style.paddingTop);
var paddingBottom = valueParseInt(pagesCls.move_el.style.paddingBottom);
pagesCls.active_style = paddingTop + "px" + " " + paddingRight + "px" + " " + paddingBottom + "px" + " " + paddingLeft + "px";
var count = 1;
var top_el = Element.getChildElement(pagesCls.move_el);
var id_name = top_el.id;
pagesCls.insertMoveRowBlockId = commonCls.getBlockid(top_el);
for (var i = 0; i < pagesCls.move_td.childNodes.length; i++) {
var div = pagesCls.move_td.childNodes[i];
if(div && div.tagName == "DIV" && Element.hasClassName(div,"cell")) {
if (Element.getChildElement(div).id  == id_name){
pagesCls.insertMoveRowIndex = count;
}
count++;
}
}
pagesCls.insertMoveRowLength = count - 1;
pagesCls.insertMoveCellIndex = commonCls.cellIndex(pagesCls.move_td) + 1;
pagesCls.insertMoveCellLength = pagesCls.move_td.parentNode.cells.length;
var parent_cell = Element.getParentElementByClassName(Element.getParentElement(el),"cell");
if(parent_cell) {
pagesCls.insertMoveRowParentId = commonCls.getBlockid(parent_cell);
} else {
pagesCls.insertMoveRowParentId = pagesCls.parentParentid;
}
pagesCls.insertMoveEndRowThreadNum = null;
pagesCls.insertMoveEndRowParentId = null;
pagesCls.xOffset = Event.pointerX(event) - Position.cumulativeOffset(pagesCls.move_el)[0];
pagesCls.yOffset = Event.pointerY(event) - Position.cumulativeOffset(pagesCls.move_el)[1];
pagesCls.start_x = Event.pointerX(event);
pagesCls.start_y = Event.pointerY(event);
pagesCls.winMoveDragGoEvent = pagesCls.winMoveDragGo.bindAsEventListener(el);
pagesCls.winMoveDragStopEvent = pagesCls.winMoveDragStop.bindAsEventListener(el);
Event.observe(document,"mousemove",pagesCls.winMoveDragGoEvent,true);
Event.observe(document,"mouseup",pagesCls.winMoveDragStopEvent,true);
Event.stop(event);
pagesCls.inMoveDrag = true;
pagesCls.insertAction = "";
},
searchInsertBlock: function(cloneTopPos, x, y, now_thread_num, now_parent_id) {
pagesCls.insertMoveEndRowParentId = now_parent_id;
var insert_el = null;
if(cloneTopPos["el"].tagName == "TR")
var insert_tr = cloneTopPos["el"];
else
var insert_tr = cloneTopPos["grouping_tr_el"];
if( x >= cloneTopPos['left'] &&
x <  cloneTopPos['right']) {
for (var i = 0,col_len = pagesCls.cloneColumnPos[now_parent_id].length; i < col_len; i++) {
var el_left = pagesCls.cloneColumnPos[now_parent_id][i]["left"];
var el_right = pagesCls.cloneColumnPos[now_parent_id][i]["right"];
if(x >= el_left &&
x <=  el_right) {
var insert_td = pagesCls.cloneColumnPos[now_parent_id][i]["el"];
for (var j = 0,row_count = pagesCls.clonePos[now_parent_id][i].length; j < row_count; j++) {
if(firstdiv_el == null) {
var firstdiv_el = pagesCls.clonePos[now_parent_id][i][j]['el'];
}
var enddiv_el = pagesCls.clonePos[now_parent_id][i][j]['el'];
if(this.clonePos[now_parent_id][i][j]['top'] > y && position_el == null) {
var position_el = pagesCls.clonePos[now_parent_id][i][j]['el'];
}
if(y >= pagesCls.clonePos[now_parent_id][i][j]['top'] &&
y <=  pagesCls.clonePos[now_parent_id][i][j]['bottom']) {
insert_el = pagesCls.clonePos[now_parent_id][i][j]['el'];
if(pagesCls.clonePos[now_parent_id][i][j]['grouping_flag']) {
var queryParams = commonCls.getParams(insert_el);
next_parent_id = parseInt(queryParams["block_id"]);
insert_el = pagesCls.searchInsertBlock(pagesCls.clonePos[now_parent_id][i][j], x,y,now_thread_num + 1,next_parent_id);
break;
}
var ex1 = pagesCls.clonePos[now_parent_id][i][j]['left'];
var ex2 = pagesCls.clonePos[now_parent_id][i][j]['right'];
var ey1= pagesCls.clonePos[now_parent_id][i][j]['top'];
var ey2 = pagesCls.clonePos[now_parent_id][i][j]['bottom'];
var direction = null;
var offset = Math.ceil((ex2 - ex1)/4);
if(x > ex2 - offset) {
direction = "right";
} else if(x < ex1 + offset) {
direction = "left";
}else if(y > ey1 + (ey2 - ey1)/2) {
direction = "bottom";
} else {
direction = "top";
}
var index = commonCls.cellIndex(insert_td);
switch (direction) {
case "left":
InsertCell(index,insert_tr);
break;
case "right":
index = index + 1;
InsertCell(index,insert_tr);
break;
case "top":
InsertBeforeEl(insert_el);
break;
case "bottom":
InsertAfterEl(insert_el);
break;
}
break;
}
}
if(insert_el == undefined || insert_el == null) {
if(position_el != null){
insert_el = position_el;
InsertBeforeEl(insert_el);
} else {
insert_el = enddiv_el;
if(enddiv_el) InsertAfterEl(enddiv_el);
}
}
break;
}
}
}
if(insert_el == null) {
if(x < cloneTopPos['left']) {
InsertCell(0,cloneTopPos["el"]);
} else {
var index = insert_tr.cells.length;
InsertCell(index,insert_tr);
}
var tdList = insert_tr.getElementsByTagName("td");
for (var i = 0,tdLen = tdList.length; i < tdLen; i++){
if(Element.hasClassName(tdList[i],"column") && (tdList[i].innerHTML.trim()) == "") {
Element.remove(tdList[i]);
}
}
}
return pagesCls.insert_el;
function InsertBeforeEl(el){
var div = document.createElement("DIV");
div.className = "cell";
div.style.padding = pagesCls.active_style;
pagesCls.insert_td = el.parentNode;
pagesCls.insert_el = el.parentNode.insertBefore(div, el);
delMoveEl();
pagesCls.insertAction = "insertrow";
}
function InsertAfterEl(el){
var div = document.createElement("DIV");
div.className = "cell";
div.style.padding = pagesCls.active_style;
pagesCls.insert_td = el.parentNode;
pagesCls.insert_el = el.parentNode.insertBefore(div, el.nextSibling);
delMoveEl();
pagesCls.insertAction = "insertrow";
}
function InsertCell(Index,insert_tr){
Index = delMoveEl(Index);
pagesCls.insert_td = insert_tr.insertCell(Index);
pagesCls.insert_td.className = "column valign-top";
var div = document.createElement("DIV");
div.className = "cell";
div.style.padding = pagesCls.active_style;
pagesCls.insert_td.appendChild(div);
pagesCls.insert_el = div;
pagesCls.insertAction = "insertcell";
}
function delMoveEl(Index) {
Index = (Index == undefined) ? 0 : Index;
if(pagesCls.move_el != null && pagesCls.move_el != undefined) {
if (Element.hasClassName(Element.getChildElement(pagesCls.move_el),"column_movedummy")){
Element.remove(pagesCls.move_el);
}
pagesCls.move_el = null;
var divList = pagesCls.move_td.getElementsByTagName("div");
if(divList.length == 0 && pagesCls.move_td.parentNode.cells.length > 1){
if(Index > commonCls.cellIndex(pagesCls.move_td)) {
Index = Index - 1;
}
Element.remove(pagesCls.move_td);
pagesCls.move_td = null;
}
}
return Index;
}
},
getSearchBlock: function(top_td_el,insert_tr,now_thread_num,now_parent_id) {
if(!pagesCls.cloneTopPos["el"]) {
var offset = Position.cumulativeOffset(top_td_el);
pagesCls.cloneTopPos["el"] = insert_tr;
pagesCls.cloneTopPos["top"] = offset[1];
pagesCls.cloneTopPos["right"] = offset[0] + top_td_el.offsetWidth;
pagesCls.cloneTopPos["bottom"] = offset[1] + top_td_el.offsetHeight;
pagesCls.cloneTopPos["left"] = offset[0];
}
pagesCls.cloneColumnPos[now_parent_id] = Array();
pagesCls.clonePos[now_parent_id] = Array();
for (var i = 0,col_len = insert_tr.childNodes.length; i < col_len; i++) {
var column_el = insert_tr.childNodes[i];
pagesCls.cloneColumnPos[now_parent_id][i] = new Object();
var offset = Position.cumulativeOffset(column_el);
pagesCls.cloneColumnPos[now_parent_id][i]["el"] = column_el;
pagesCls.cloneColumnPos[now_parent_id][i]["top"] = offset[1];
pagesCls.cloneColumnPos[now_parent_id][i]["right"] = offset[0] + column_el.offsetWidth;
pagesCls.cloneColumnPos[now_parent_id][i]["bottom"] = offset[1] + column_el.offsetHeight;
pagesCls.cloneColumnPos[now_parent_id][i]["left"] = offset[0];
pagesCls.clonePos[now_parent_id][i] = Array();
for (var j = 0,row_len = column_el.childNodes.length; j < row_len; j++) {
var row_el = column_el.childNodes[j];
var child_el = Element.getChildElement(row_el);
pagesCls.clonePos[now_parent_id][i][j] = new Object();
pagesCls.clonePos[now_parent_id][i][j]["grouping_flag"] = false;
if(Element.hasClassName(child_el, "module_grouping_box")) {
var queryParams = commonCls.getParams(row_el);
next_parent_id = parseInt(queryParams["block_id"]);
var tdSubList = child_el.getElementsByTagName("td");
for (var k = 0,tdSubListLen = tdSubList.length; k < tdSubListLen; k++){
if(Element.hasClassName(tdSubList[k],"column")) {
var now_insert_tr = Element.getParentElement(tdSubList[k]);
break;
}
}
pagesCls.clonePos[now_parent_id][i][j]["grouping_flag"] = true;
pagesCls.clonePos[now_parent_id][i][j]["grouping_tr_el"] = now_insert_tr;
if(now_insert_tr) pagesCls.getSearchBlock(child_el, now_insert_tr, now_thread_num + 1,next_parent_id);
}
var offset = Position.cumulativeOffset(child_el);
pagesCls.clonePos[now_parent_id][i][j]["el"] = row_el;
pagesCls.clonePos[now_parent_id][i][j]["top"] = offset[1];
pagesCls.clonePos[now_parent_id][i][j]["right"] = offset[0] + child_el.offsetWidth;
pagesCls.clonePos[now_parent_id][i][j]["bottom"] = offset[1] + child_el.offsetHeight;
pagesCls.clonePos[now_parent_id][i][j]["left"] = offset[0];
}
}
},
winMoveDragGo: function(event) {
if(!pagesCls.inMoveDrag)
return false;
pagesCls.insert_td = null;
pagesCls.insert_el = null;
var x = Event.pointerX(event);
var y = Event.pointerY(event);
var width = this.offsetWidth;
var height = this.offsetHeight;
var def_px = 5;
if(x <= pagesCls.start_x + def_px && x >= pagesCls.start_x - def_px &&
y <= pagesCls.start_y + def_px && y >= pagesCls.start_y - def_px) {
return true;
}
commonCls.scrollMoveDrag(event);
if(pagesCls.active == null || pagesCls.active == undefined) {
var divList = document.getElementsByTagName("div");
for (var i = 0; i < divList.length; i++){
if (Element.hasClassName(divList[i],"cell")){
pagesCls.cancelSelectStyle(divList[i]);
}
}
pagesCls.active = pagesCls.winCreateCopy(this,width,height);
for (var key in pagesCls.inMoveShowHeader) {
if(pagesCls.inMoveShowHeader[key]) {
var header_el = $("_move_header" + key);
Element.addClassName(header_el, "display-none");
pagesCls.inMoveShowHeader[key] = false;
}
}
if (Element.hasClassName(pagesCls.move_column_el,"main_column")){
pagesCls.getSearchBlock(pagesCls.move_column_el, pagesCls.insert_tr,0,0);
} else {
pagesCls.getSearchBlock(pagesCls.move_column_el, pagesCls.insert_tr,pagesCls.parentThreadNum,pagesCls.parentParentid);
}
}
if (Element.hasClassName(pagesCls.move_column_el,"main_column")){
pagesCls.searchInsertBlock(pagesCls.cloneTopPos, x, y, 0, 0);
} else {
pagesCls.searchInsertBlock(pagesCls.cloneTopPos, x, y, pagesCls.parentThreadNum, pagesCls.parentParentid);
}
if(pagesCls.insert_td && pagesCls.insert_el) {
pagesCls.move_td = pagesCls.insert_td;
pagesCls.move_el = pagesCls.insert_el;
}
Event.stop(event);
var paddingLeft = valueParseInt(this.style.paddingLeft);
var paddingTop = valueParseInt(this.style.paddingTop);
if(Event.pointerX(event) - pagesCls.xOffset + paddingLeft <= 0){
pagesCls.active.style.left = -paddingLeft + "px";
}else{
pagesCls.active.style.left = (x - pagesCls.xOffset) + "px";
}
if(Event.pointerY(event) - pagesCls.yOffset + paddingTop <= 0){
pagesCls.active.style.top  = -paddingTop + "px";
}else{
pagesCls.active.style.top  = (y - pagesCls.yOffset) + "px";
}
if(pagesCls.move_el && (Element.getChildElement(pagesCls.move_el) == null || Element.getChildElement(pagesCls.move_el).innerHTML == "")) {
var div = document.createElement("DIV");
var div_child = document.createElement("DIV");
Element.addClassName(div,"column_movedummy");
div_child.style.width = pagesCls.active.offsetWidth - valueParseInt(pagesCls.move_el.style.paddingLeft) - valueParseInt(pagesCls.move_el.style.paddingRight) + "px";
div_child.style.height = pagesCls.active.offsetHeight - valueParseInt(pagesCls.move_el.style.paddingTop) - valueParseInt(pagesCls.move_el.style.paddingBottom) + "px";
div.appendChild(div_child);
pagesCls.move_el.appendChild(div);
if(browser.isNS){
pagesCls.move_column_el.style.display = "none";
pagesCls.move_column_el.style.display = "";
}
}
},
winMoveDragStop: function(event) {
if(pagesCls.active != null && pagesCls.active != undefined) {
var _move_header = $("_move_header" + Element.getChildElement(pagesCls.active, 2).id);
if(Element.hasClassName(_move_header, "_move_header")) {
_move_header.style.display = "";
}
Element.remove(Element.getChildElement(pagesCls.move_el));
pagesCls.move_el.appendChild(Element.getChildElement(pagesCls.active,2));
pagesCls.cancelSelectStyle(pagesCls.move_el);
Element.remove(pagesCls.active);
delete pagesCls.active;
pagesCls.active = null;
}
Event.stopObserving(document,"mousemove",pagesCls.winMoveDragGoEvent,true);
Event.stopObserving(document,"mouseup",pagesCls.winMoveDragStopEvent,true);
pagesCls.winMoveDragGoEvent = null;
pagesCls.winMoveDragStopEvent = null;
pagesCls.cloneTopPos = new Object();
pagesCls.cloneColumnPos = Array();
pagesCls.clonePos = Array();
Event.stop(event);
if(pagesCls.insertMoveEndRowParentId != null) {
var id_name = Element.getChildElement(pagesCls.move_el).id;
var insertCellIndex = commonCls.cellIndex(pagesCls.move_td) + 1;
var insertRowIndex = 1;
var insertRowLength = 1;
if(pagesCls.insertAction == "insertrow") {
var count = 1;
for (var i = 0; i < pagesCls.move_td.childNodes.length; i++) {
var div = pagesCls.move_td.childNodes[i];
if(div.tagName == "DIV" && Element.hasClassName(div,"cell")) {
if (Element.getChildElement(div).id  == id_name){
insertRowIndex = count;
}
count++;
}
}
insertRowLength = count - 1;
if(insertRowLength == 1)
pagesCls.insertAction = "insertcell";
}
if(pagesCls.insertAction == "" || pagesCls.insertMoveCellIndex == insertCellIndex &&
pagesCls.insertMoveRowIndex == insertRowIndex &&
pagesCls.insertMoveRowLength == insertRowLength &&
pagesCls.insertMoveRowParentId == pagesCls.insertMoveEndRowParentId) {
} else {
if(pagesCls.insertMoveRowLength == 1 && pagesCls.insertMoveCellLength == 1 && pagesCls.insertMoveRowParentId != 0) {
pagesCls.delEmptyBlock(pagesCls.insertMoveRowParentId);
}
var queryParams = commonCls.getParams(pagesCls.move_el);
var page_id = queryParams["page_id"];
var postBody = "pages_action_" + pagesCls.insertAction +
"&block_id=" + pagesCls.insertMoveRowBlockId +
"&col_num=" + insertCellIndex + "&row_num=" + insertRowIndex + "&row_len=" + insertRowLength +
"&parent_id=" + pagesCls.insertMoveEndRowParentId +
"&pre_col_num=" + pagesCls.insertMoveCellIndex + "&pre_row_num=" + pagesCls.insertMoveRowIndex + "&pre_row_len=" + pagesCls.insertMoveRowLength +
"&pre_parent_id=" + pagesCls.insertMoveRowParentId +
"&_show_count=" + pagesCls.show_count[page_id];
var params = new Object();
params["method"] = "post";
params["param"] = postBody;
params["top_el"] = pagesCls.move_el;
params["callbackfunc_error"] = function(){location.reload();};
params["token"] = pagesCls.pages_token[page_id];
commonCls.send(params);
pagesCls.show_count[page_id]++;
}
}
pagesCls.inMoveDrag = false;
},
delEmptyBlock: function(del_parent_id) {
var divList = pagesCls.move_column_el.getElementsByTagName("div");
for (var i = 0; i < divList.length; i++){
if (Element.hasClassName(divList[i],"cell")){
var id_name = Element.getChildElement(divList[i]).id;
var block_id = id_name.substr(1, id_name.length);
if(block_id == del_parent_id) {
var tr_el = Element.getParentElement(divList[i],2);
var td_el = divList[i].parentNode;
var parent_cell = Element.getParentElementByClassName(Element.getParentElement(divList[i]),"cell");
if(parent_cell) {
var parent_id_name = Element.getChildElement(parent_cell).id;
var parent_id = parent_id_name.substr(1, parent_id_name.length);
} else {
var parent_id = 0;
}
Element.remove(divList[i]);
var count = 0;
for (var i = 0; i < td_el.childNodes.length; i++) {
var div = td_el.childNodes[i];
if(div && div.tagName == "DIV" && Element.hasClassName(div,"cell")) {
count++;
}
}
if(count == 0)
Element.remove(td_el);
if(tr_el.cells.length == 0 && parent_id != 0) {
pagesCls.delEmptyBlock(parent_id);
}
}
}
}
},
pageMoveComplete: function(transport) {
var res = transport.responseText;
if(res != "" && res != null) {
res = res.replace(/\\n/ig,"\n");
alert(res);
window.location.reload();
}
},
winCreateCopy: function(el,width,height) {
var Block = document.createElement("div");
var top_el_id = Element.getChildElement(el).id;
var _move_header = $("_move_header" + top_el_id);
Block.appendChild(el);
if(_move_header && !Element.hasClassName(_move_header, "display-none")) {
_move_header.style.position = "absolute";
var paddingLeft = valueParseInt(el.style.paddingLeft);
var paddingTop = valueParseInt(el.style.paddingTop);
_move_header.style.top = paddingTop + "px";
_move_header.style.left = paddingLeft + "px";
Element.removeClassName(_move_header, "display-none");
_move_header.style.display = "block";
}
Block.style.position = "absolute";
Block.style.zIndex = commonCls.max_zIndex + 1;
Block.style.width = width + "px";
Block.style.height = height + "px";
document.body.appendChild(Block);
return Block;
},
onGroupingEvent: function(event) {
if(_nc_layoutmode != "on") return;
var cell_el = Element.getParentElementByClassName(Event.element(event),"cell");
if(!cell_el)
return false;
if(!pagesCls.cancelSelectStyle(cell_el)) {
pagesCls.setSelectStyle(cell_el);
pagesCls.onGroupingCheck(cell_el);
}
},
onGroupingCheck : function(cell_el) {
var main_column_el = Element.getParentElementByClassName(cell_el,"main_column");
var grouping_flag = false;
if(!main_column_el) {
main_column_el = Element.getChildElement(document.body);
grouping_flag = true;
}
var parent_id = this.getParentid(cell_el);
var divList = document.getElementsByTagName("div");
this.groupingList = Array();
var groupingBlockidList = Array();
var col_num = -1;
var row_num = 0;
var count = 0;
var cell_index = null;
var pre_cell_index = null;
for (var i = 0; i < divList.length; i++){
if (Element.hasClassName(divList[i],"cell")){
var now_parent_id = this.getParentid(divList[i]);
if(pagesCls.checkSelectStyle(divList[i])) {
if(now_parent_id != parent_id) {
pagesCls.cancelSelectStyle(divList[i]);
} else if(grouping_flag == false && main_column_el != Element.getParentElementByClassName(divList[i],"main_column")) {
pagesCls.cancelSelectStyle(divList[i]);
} else {
cell_index = commonCls.cellIndex(divList[i].parentNode);
if(pre_cell_index == null || cell_index != pre_cell_index) {
pre_cell_index = cell_index;
col_num++;
row_num = 0;
this.groupingList[col_num] = Array();
groupingBlockidList[col_num] =  Array();
}
queryParams = commonCls.getParams(divList[i]);
var block_id = queryParams["block_id"];
groupingBlockidList[col_num][row_num] = block_id;
this.groupingList[col_num][row_num] = divList[i];
row_num++;
count++;
}
}
}
}
return groupingBlockidList;
},
setSelectStyle: function(el) {
var main_column = Element.getParentElementByClassName(el,"main_column");
var grouping_flag = false;
if(!main_column) {
main_column = Element.getChildElement(document.body);
grouping_flag = true;
}
if(main_column.id =="__leftcolumn") {
Element.addClassName(el, "select_leftcolumn");
} else if(main_column.id =="__centercolumn") {
Element.addClassName(el, "select_centercolumn");
} else if(main_column.id =="__rightcolumn") {
Element.addClassName(el, "select_rightcolumn");
} else if(main_column.id =="__headercolumn") {
Element.addClassName(el, "select_headercolumn");
} else if(grouping_flag) {
Element.addClassName(el, "select_centercolumn");
} else {
return false;
}
return true;
},
cancelSelectStyle: function(el) {
var style_name = this.checkSelectStyle(el);
if(!style_name) {
return false;
}
Element.removeClassName(el, style_name);
return true;
},
checkSelectStyle: function(el) {
if(Element.hasClassName(el,"select_leftcolumn")) {
var style_name = "select_leftcolumn";
} else if(Element.hasClassName(el,"select_centercolumn")) {
var style_name = "select_centercolumn";
} else if(Element.hasClassName(el,"select_rightcolumn")) {
var style_name = "select_rightcolumn";
} else if(Element.hasClassName(el,"select_headercolumn")) {
var style_name = "select_headercolumn";
} else {
return false;
}
return style_name;
},
getThreadnum: function(cell_el, thread_num) {
thread_num = (thread_num == undefined) ? pagesCls.parentThreadNum : thread_num;
var parent_cell = Element.getParentElementByClassName(Element.getParentElement(cell_el),"cell");
if(parent_cell) {
thread_num++;
thread_num = this.getThreadnum(parent_cell,thread_num);
}
return thread_num;
},
getParentid: function(cell_el) {
var parent_cell = Element.getParentElementByClassName(Element.getParentElement(cell_el),"cell");
if(parent_cell) {
var queryParams = commonCls.getParams(parent_cell);
return queryParams["block_id"];
}
return 0;
},
addGrouping: function(event, confirm_mes , confirm_error_mes) {
var divList = document.getElementsByTagName("div");
var cell_el = null;
for (var i = 0; i < divList.length; i++){
if (Element.hasClassName(divList[i],"cell")){
if(pagesCls.checkSelectStyle(divList[i])) {
cell_el = divList[i];
var queryParams = commonCls.getParams(cell_el);
var ins_block_id = queryParams["block_id"];
var page_id = queryParams["page_id"];
break;
}
}
}
if(cell_el == null) {
commonCls.alert(confirm_error_mes);
return;
}
if (confirm_mes == undefined || !commonCls.confirm(confirm_mes)) return false;
var groupingBlockidList = this.onGroupingCheck(cell_el);
for(var i = 0; i < this.groupingList.length; i++) {
for(var j = 0; j < this.groupingList[i].length; j++) {
pagesCls.cancelSelectStyle(this.groupingList[i][j]);
}
}
var postBody = "pages_action_grouping&block_id=" + ins_block_id +
"&_show_count=" + pagesCls.show_count[page_id];
postBody = postBody + "&_grouping_list=";
for(var i = 0; i < groupingBlockidList.length; i++) {
for(var j = 0; j < groupingBlockidList[i].length; j++) {
postBody = postBody + groupingBlockidList[i][j];
if(j != groupingBlockidList[i].length - 1)
postBody = postBody + ",";
}
if(i != groupingBlockidList.length - 1)
postBody = postBody + ":";
}
var params_grouping = new Object();
var div = document.createElement("DIV");
div.className = "cell";
var paddingLeft = valueParseInt(cell_el.style.paddingLeft);
var paddingRight = valueParseInt(cell_el.style.paddingRight);
var paddingTop = valueParseInt(cell_el.style.paddingTop);
var paddingBottom = valueParseInt(cell_el.style.paddingBottom);
div.style.padding = paddingTop + "px" + " " + paddingRight + "px" + " " + paddingBottom + "px" + " " + paddingLeft + "px";
var temp_html = cell_el.innerHTML;
div.appendChild(Element.getChildElement(cell_el));
cell_el.innerHTML = temp_html;
pagesCls.active = div;
params_grouping["method"] = "post";
params_grouping["param"] = postBody;
params_grouping["loading_el"] = Element.getChildElement(cell_el);
params_grouping["target_el"] = cell_el;
params_grouping["top_el"] = cell_el;
params_grouping["callbackfunc"] = function(cell_el){
this.addGroupingComp(cell_el);
}.bind(this);
params_grouping["func_param"] = cell_el;
params_grouping["callbackfunc_error"] = function(res){
pagesCls.active = null; location.reload();
};
params_grouping["token"] = pagesCls.pages_token[page_id];
commonCls.send(params_grouping);
pagesCls.show_count[page_id]++;
return false;
},
addGroupingComp: function(cell_el) {
var main_column_el = Element.getParentElementByClassName(cell_el,"main_column");
if(!main_column_el) {
main_column_el = Element.getChildElement(document.body);
}
var insert_column_el = Element.getChildElementByClassName(cell_el,"column");
var insert_tr = Element.getParentElement(insert_column_el);
for(var i = 0; i < this.groupingList.length; i++) {
for(var j = 0; j < this.groupingList[i].length; j++) {
if(this.groupingList[i][j] == cell_el) {
var append_el = pagesCls.active;
} else {
var append_el = this.groupingList[i][j];
}
if(i==0) {
insert_column_el.appendChild(append_el);
} else {
if(i > insert_tr.cells.length - 1) {
insert_column_el = insert_tr.insertCell(i);
insert_column_el.className = "column valign-top";
}
insert_column_el.appendChild(append_el);
}
}
}
pagesCls.active = null;
var tdList = main_column_el.getElementsByTagName("td");
for (var i = 0; i < tdList.length; i++){
if (Element.hasClassName(tdList[i],"column")){
var count = 0;
for (var j = 0; j < tdList[i].childNodes.length; j++) {
var div = tdList[i].childNodes[j];
if(div && div.tagName == "DIV" && Element.hasClassName(div,"cell")) {
count++;
}
}
if(count == 0) {
Element.remove(tdList[i]);
}
}
}
return true;
},
cancelGrouping: function(event, confirm_mes , confirm_error_mes) {
var divList = document.getElementsByTagName("div");
confirm_flag = false;
for (var i = 0; i < divList.length; i++){
if (Element.hasClassName(divList[i],"cell")){
if(pagesCls.checkSelectStyle(divList[i])) {
var queryParams = commonCls.getParams(divList[i]);
if(queryParams["action"] == "pages_view_grouping" || queryParams["action"] == "pages_action_grouping") {
if(confirm_flag == false) {
if (confirm_mes == undefined || !commonCls.confirm(confirm_mes)) return false;
confirm_flag = true;
}
var cell_el = divList[i];
this.cancelGroupingDetail(cell_el);
} else {
pagesCls.cancelSelectStyle(divList[i]);
}
}
}
}
if(confirm_flag == false) {
commonCls.alert(confirm_error_mes);
}
},
cancelGroupingDetail: function(cell_el) {
if(this.inCancelGroupingDrag == true ) {
setTimeout(this.cancelGroupingDetail(cell_el), 100);
}
this.inCancelGroupingDrag = true;
var queryParams = commonCls.getParams(cell_el);
var ins_block_id = queryParams["block_id"];
var page_id = queryParams["page_id"];
var postBody = "pages_action_cancelgrouping&block_id=" + ins_block_id +
"&_show_count=" + pagesCls.show_count[page_id];
var params_grouping = new Object();
params_grouping["method"] = "post";
params_grouping["param"] = postBody;
params_grouping["loading_el"] = Element.getChildElement(cell_el);
params_grouping["top_el"] = cell_el;
params_grouping["callbackfunc"] = function(cell_el){this.cancelGroupingComp(cell_el);}.bind(this);
params_grouping["func_param"] = cell_el;
params_grouping["callbackfunc_error"] = function(){location.reload();};
params_grouping["token"] = pagesCls.pages_token[page_id];
commonCls.send(params_grouping);
pagesCls.show_count[page_id]++;
return false;
},
cancelGroupingComp: function(parent_cell_el) {
this.inCancelGroupingDrag = false;
var top_el = Element.getChildElement(parent_cell_el);
var parent_column_el = Element.getParentElementByClassName(top_el,"column");
var first_column_el = Element.getChildElementByClassName(top_el,"column");
var tr_el = first_column_el.parentNode;
var divList = parent_cell_el.getElementsByTagName("div");
for (var i = 0; i < divList.length; i++){
if (Element.hasClassName(divList[i],"cell")){
pagesCls.cancelSelectStyle(divList[i]);
}
}
var tdList = Array();
var count_td = 0;
for (var i = 0; i < tr_el.childNodes.length; i++) {
var column_el = tr_el.childNodes[i];
if(column_el && column_el.tagName == "TD" && Element.hasClassName(column_el,"column")) {
if(column_el == first_column_el) {
var divList = Array();
var count = 0;
for (var j = 0; j < column_el.childNodes.length; j++) {
var div = column_el.childNodes[j];
if(div && div.tagName == "DIV" && Element.hasClassName(div,"cell")) {
divList[count] = div;
count++;
}
}
for (var j = divList.length - 1; j >= 0; j--) {
parent_column_el.insertBefore(divList[j], parent_cell_el.nextSibling);
}
} else {
tdList[count_td] = column_el;
count_td++;
}
}
}
for (var i = tdList.length - 1; i >= 0; i--) {
parent_column_el.parentNode.insertBefore(tdList[i], parent_column_el.nextSibling);
}
Element.remove(parent_cell_el);
}
}
pagesCls = new clsPages();
var clsBlockstyle = Class.create();
clsBlockstyle.prototype = {
initialize: function() {
this.confirm_mes = null;
this.colorclick_flag = new Object();
this.color_params = new Object();
this.change_color = new Object();
this.refresh_flag = new Object();
},
init: function(id, active_tab, blocktheme_name, lang_style, lang_theme, lang_coloration) {
var top_el = $(id);
this.refresh_flag[id] = false;
if(blocktheme_name) {
commonCls.addBlockTheme(blocktheme_name);
}
tabset = new compTabset(top_el);
tabset.setActiveIndex(valueParseInt(active_tab));
tabset.addTabset(lang_theme);
tabset.addTabset(lang_style,blockstyleCls.clkStyle.bind($(id)));
tabset.addTabset(lang_coloration, blockstyleCls.clkCustomColor.bind($(id)), blockstyleCls.initCustomColor.bind($(id)));
tabset.render();
},
clkStyle: function() {
var top_el = this;
var form = top_el.getElementsByTagName("form")[0];
commonCls.focus(form.block_name);
},
clkCustomColor: function() {
var el = $("_blockstyle_custom_top" + this.id);
if(el) commonCls.focus(el);
},
initCustomColor: function() {
var id = this.id;
var samplefields = Element.getElementsByClassName($(id), "_blockstyle_custom_sample");
samplefields.each(function(coloration_el) {
var preview_el = Element.getChildElement(coloration_el);
var content_el = coloration_el.nextSibling;
var propertyfields = Element.getElementsByClassName(content_el, "_blockstyle_custom_property");
propertyfields.each(function(field) {
var child_el = Element.getChildElement(field);
var property_name = child_el.value;
if(pagestyleCls.setHighlightColor(preview_el, field, property_name)) {
return;
}
}.bind(this));
}.bind(this));
var checkboxfields = Element.getElementsByClassName($(id), "_blockstyle_autocheckbox");
checkboxfields.each(function(checkbox_el) {
checkbox_el.checked = true;
blockstyleCls.chkAutoClick(checkbox_el);
}.bind(this));
},
chkAutoClick: function(this_el) {
var input_el = this_el.parentNode.parentNode.previousSibling;
if(this_el.checked) {
input_el.disabled = true;
} else {
input_el.disabled = false;
input_el.focus();
input_el.select();
}
},
colorClick: function(top_id, id, id_name, theme_name, property_name, color, this_el, count_color) {
count_color = (count_color == undefined || count_color == null) ? 0 : count_color;
var top_el = $(id);
if(!top_el) {
return;
}
if(this_el.tagName.toLowerCase() != "select" && (color != "transparent" && (Element.hasClassName(this_el,"highlight") || (color == null || color.length != 7 || color.indexOf('#') != 0)))) {
return;
}
this.colorclick_flag[top_id] = true;
var blockstyle_all_apply_el = $("blockstyle_all_apply" + top_id);
var append_class_name = "";
if( this.change_color[top_id]) {
var old_change_color = this.change_color[top_id];
} else {
var old_change_color = "";
}
if(!blockstyle_all_apply_el || !blockstyle_all_apply_el.checked) {
append_class_name = ".blockstyle" + top_id + " ";
this.change_color[top_id] = "once";
} else {
this.change_color[top_id] = "all";
}
if(old_change_color != "" && this.change_color[top_id] != old_change_color) {
this.refresh_flag[top_id] = true;
}
var el = $(id_name);
var class_arr = el.className.split(" ");
var class_name = "";
class_arr.each(
function(value){
class_name += "." + value + " ";
}
);
class_name = append_class_name + class_name;
var style = null;
var styleList = document.getElementsByTagName("style");
for (var i = 0; i < styleList.length; i++){
if (styleList[i].title == "_blockstyle_custom_style" + theme_name){
style = styleList[i];
break;
}
}
if(!style) {
if(typeof document.createStyleSheet != 'undefined') {
var style=document.createStyleSheet();
} else {
var style=document.createElement('STYLE');
style.appendChild(document.createTextNode(''));
style.type="text/css";
var oHEAD=document.getElementsByTagName('HEAD').item(0);
oHEAD.appendChild(style);
}
style.title = "_blockstyle_custom_style" + theme_name;
}
if(typeof style.addRule != 'undefined') {
style.addRule(class_name, property_name + ":" + color);
if(property_name == "backgroundColor" || property_name == "background-color") {
style.addRule(class_name, "background-image:none;");
}
} else if(typeof style.styleSheet != 'undefined' && typeof style.styleSheet.addRule != 'undefined') {
style.styleSheet.addRule(class_name, property_name + ":" + color);
if(property_name == "backgroundColor" || property_name == "background-color") {
style.styleSheet.addRule(class_name, "background-image:none;");
}
} else {
if(property_name == "background" && color == "") {
color = "none";
}
if(typeof style.sheet.insertRule != 'undefined') {
style.sheet.insertRule(class_name + "{" + property_name + ":" + color + "}", style.sheet.cssRules.length);
if(property_name == "backgroundColor" || property_name == "background-color") {
style.sheet.insertRule(class_name + "{background-image:none;}", style.sheet.cssRules.length);
}
} else {
style.innerHTML = style.innerHTML + class_name + "{" + property_name + ":" + color + "}\n";
if(property_name == "backgroundColor" || property_name == "background-color") {
style.innerHTML = style.innerHTML + class_name + "{background-image:none;}\n";
}
}
}
if(browser.isGecko) {
if(property_name.match(/^border/)) {
var samplefields = Element.getElementsByClassName(document, class_arr[class_arr.length-1]);
samplefields.each(function(class_el) {
if(class_el.tagName == "TABLE" || class_el.tagName == "TR" || class_el.tagName == "TD") {
commonCls.displayChange(class_el);
setTimeout(function(){commonCls.displayChange(class_el);}.bind(this), 100);
}
}.bind(this));
}
}
if(!this.color_params[theme_name]) {
this.color_params[theme_name] = new Object();
this.color_params[theme_name]['color'] = new Object();
}
if(!this.color_params[theme_name]['color'][el.className]) {
this.color_params[theme_name]['color'][el.className] = new Object();
}
this.color_params[theme_name]['color'][el.className][property_name] = color;
if(property_name == "background-color" && this.color_params[theme_name]['color'][el.className]['background']) {
this.color_params[theme_name]['color'][el.className]['background'] = null;
this.color_params[theme_name]['color'][el.className]['background-image'] = "none";
}
var rgb = commonCls.getRGBtoHex(color);
var hsl = commonCls.getHSL(rgb.r,rgb.g,rgb.b);
var set_color_flag = false;
var inputList = top_el.getElementsByTagName("input");
for (var i = 0; i < inputList.length; i++){
if(inputList[i].disabled == true) {
var ref_class_name_el = inputList[i].previousSibling;
var ref_class_name = ref_class_name_el.value;
var same_flag = false;
if(ref_class_name.match(/^same:/)) {
same_flag = true;
ref_class_name = ref_class_name.replace(/^same:/, "");
}
var ref_property_name_el = ref_class_name_el.previousSibling;
var current_el = Element.getChildElement(inputList[i].parentNode);
var current_property_name = current_el.value;
if(el.className == ref_class_name &&
property_name == ref_property_name_el.value) {
var buf_count_color = count_color;
var change_flag = false;
if(buf_count_color) {
while(buf_count_color > 0) {
if(current_el) {
current_el = current_el.nextSibling;
buf_count_color--;
} else {
break;
}
}
if(current_el && Element.hasClassName(current_el,"_blocktheme_box")) {
current_el.onclick();
change_flag = true;
}
}
if(!change_flag) {
if(same_flag) {
var set_color = color;
} else if(current_property_name == "background-color") {
var new_hsl_s = hsl.s;//+80;
var new_hsl_l = 240;
var new_rgb = commonCls.getRBG(hsl.h, new_hsl_s, new_hsl_l);
var set_color = commonCls.getHex(new_rgb.r,new_rgb.g,new_rgb.b);
} else {
var new_hsl_s = hsl.s;
var new_hsl_l = 124;
var new_rgb = commonCls.getRBG(hsl.h,new_hsl_s,new_hsl_l);
var set_color = commonCls.getHex(new_rgb.r,new_rgb.g,new_rgb.b);
}
inputList[i].value = set_color;
inputList[i].onchange();
}
}
}
}
pagestyleCls.setHighlight(this_el);
},
blockstyleSubmit: function(id, block_id, inside_flag, current_theme_name, confirm_mes, winclose_flag) {
this.confirm_mes = confirm_mes;
var winclose_flag = (winclose_flag == undefined || winclose_flag == null) ? true : winclose_flag;
var top_el = $(id);
if(!top_el) {
return;
}
var form = top_el.getElementsByTagName("form")[0];
var param = "action=dialog_blockstyle_action_edit_init&block_id="+ block_id + "&"+ Form.serialize(form);
var current_queryParams = param.parseQuery();
if(this.color_params[current_theme_name] &&
this.color_params[current_theme_name]['color']) {
current_queryParams = Object.extend(current_queryParams, this.color_params[current_theme_name]);
}
if((current_queryParams['pre_block_name'] != current_queryParams['block_name'] ||
current_queryParams['pre_theme_kind'] != current_queryParams['theme_kind'] ||
current_queryParams['pre_template_kind'] != current_queryParams['template_kind'] ||
current_queryParams['pre_minwidthsize'] != current_queryParams['minwidthsize'] ||
current_queryParams['pre_topmargin'] != current_queryParams['topmargin'] ||
current_queryParams['pre_rightmargin'] != current_queryParams['rightmargin'] ||
current_queryParams['pre_bottommargin'] != current_queryParams['bottommargin'] ||
current_queryParams['pre_leftmargin'] != current_queryParams['leftmargin']) ||
current_queryParams['color']) {
var theme_params = new Object();
var return_param = new Object();
return_param['id'] = id;
return_param['winclose_flag'] = winclose_flag;
return_param['param'] = current_queryParams;
return_param['block_name'] = current_queryParams['block_name'];
return_param['pre_block_name'] = current_queryParams['pre_block_name'];
return_param['topmargin'] = current_queryParams['topmargin'];
return_param['rightmargin'] = current_queryParams['rightmargin'];
return_param['bottommargin'] = current_queryParams['bottommargin'];
return_param['leftmargin'] = current_queryParams['leftmargin'];
return_param['pre_theme_kind'] = current_queryParams['pre_theme_kind'];
return_param['pre_template_kind'] = current_queryParams['pre_template_kind'];
return_param['theme_kind'] = current_queryParams['theme_kind'];
return_param['template_kind'] = current_queryParams['template_kind'];
return_param['pre_minwidthsize'] = current_queryParams['pre_minwidthsize'];
return_param['minwidthsize'] = current_queryParams['minwidthsize'];
return_param['inside_flag'] = inside_flag;
theme_params["method"] = "post";
theme_params["param"] = current_queryParams;
theme_params["top_el"] = top_el;
theme_params["loading_el"] = top_el;
theme_params["callbackfunc"] = function(return_param,res){this.themeChangeComplete(return_param,res);}.bind(this);
theme_params["callbackfunc_error"] = function(return_param,res){this.themeChangeComplete(return_param,res);}.bind(this);
theme_params["func_param"] = return_param;
theme_params["func_error_param"] = return_param;
commonCls.send(theme_params);
} else {
if(winclose_flag) {
form.cancel.onclick();
}
}
},
themeChangeComplete: function(return_param, res) {
var top_el = $(return_param['id']);
var parent_el = Element.getChildElementByClassName(top_el,"blockstyle_parent_id_name");
if(parent_el && parent_el.value) {
var parent_top_el = $(parent_el.value);
var parent_id = parent_el.value;
} else {
var parent_top_el = null;
}
var form = top_el.getElementsByTagName("form")[0];
if(res == "") {
var queryParams = return_param['param'];//.parseQuery();
if(parent_id) {
var url = commonCls.cutParamByUrl(commonCls.getUrl(parent_id)).parseQuery();
url["active_tab"] = 1;
if(url['action'] == "pages_action_grouping") {
url['action'] = "pages_view_grouping";
}
}
if(parent_top_el) {
if(parent_top_el.parentNode.tagName != "BODY") {
parent_top_el.parentNode.style.padding = return_param['topmargin'] + "px" + " " + return_param['rightmargin'] + "px" + " " + return_param['bottommargin'] + "px" + " " + return_param['leftmargin'] + "px";
}
form.pre_block_name.value = return_param['block_name'];
form.pre_theme_kind.value = return_param['theme_kind'];
form.pre_template_kind.value = return_param['template_kind'];
form.pre_minwidthsize.value = return_param['minwidthsize'];
form.pre_topmargin.value = return_param['topmargin'];
form.pre_rightmargin.value = return_param['rightmargin'];
form.pre_bottommargin.value = return_param['bottommargin'];
form.pre_leftmargin.value = return_param['leftmargin'];
if(return_param['pre_block_name'] != return_param['block_name'] ||
return_param['pre_theme_kind'] != return_param['theme_kind'] ||
return_param['pre_template_kind'] != return_param['template_kind'] ||
return_param['pre_minwidthsize'] != return_param['minwidthsize']) {
if(parent_top_el.parentNode.tagName == "BODY" || this.refresh_flag[return_param['id']] == true) {
location.reload();
return false;
} else if(return_param['pre_template_kind'] != return_param['template_kind']) {
location.reload();
return false;
}
if(return_param['inside_flag']) {
if(return_param['winclose_flag'] == true) {
form.cancel.onclick();
} else {
commonCls.sendView(return_param['id'], url, null, true);
}
} else {
var win_params = new Object();
win_params["method"] = "get";
win_params["param"] = url;
win_params["loading_el"] = parent_top_el;
win_params["target_el"] = parent_top_el.parentNode;
win_params["callbackfunc"] = function(top_el){commonCls.moveVisibleHide(top_el);}.bind(this);
win_params["func_param"] = top_el;
commonCls.send(win_params);
if(return_param['winclose_flag'] == true) {
form.cancel.onclick();
}
}
} else {
if(this.refresh_flag[return_param['id']] == true) {
location.reload();
return true;
}
if(return_param['winclose_flag'] == true) {
form.cancel.onclick();
}
}
}
}else {
if(res.match(":")) {
var mesArr = res.split(":");
var alert_res = "";
for(var i = 1; i < mesArr.length; i++) {
alert_res += mesArr[i];
}
var elements = Form.getElements(form);
for (var i = 0; i < elements.length; i++) {
if (elements[i]) {
if(elements[i].name == mesArr[0]) {
try {
commonCls.alert(mesArr[1]);
elements[i].focus();
if(elements[i].type == "text")
elements[i].select();
} catch (e) {}
break;
}
}
}
} else {
commonCls.alert(res);
}
}
},
themeClick: function(id, inside_flag, this_el, theme_name) {
var blocktheme_top = Element.getChildElementByClassName($(id), "_blocktheme_top");
var themefields = Element.getElementsByClassName(blocktheme_top, "_blocktheme");
var return_flag = false;
var highlight_flag = false;
themefields.each(function(field) {
if(Element.hasClassName(field,"highlight")) {
highlight_flag = true;
if(field == this_el) {
return_flag = true;
return;
} else {
Element.removeClassName(field,"highlight");
}
}
}.bind(this));
if(return_flag || (theme_name == "_auto" && highlight_flag == false)) {
return;
}
Element.addClassName(this_el,"highlight");
var top_el = $(id);
if(!inside_flag) {
var parent_el = Element.getChildElementByClassName(top_el,"blockstyle_parent_id_name");
if(parent_el && parent_el.value) {
var parent_top_el = $(parent_el.value);
var parent_id = parent_el.value;
}
} else {
var parent_id = id;
}
if(!$(parent_id)) {
return;
}
var url = commonCls.cutParamByUrl(commonCls.getUrl(parent_id)).parseQuery();
if(url['action'] == "pages_action_grouping") {
url['action'] = "pages_view_grouping";
}
url['blocktheme_name'] = theme_name;
url['active_tab'] = 0;
var send_params = new Object();
send_params["callbackfunc"] = function(res){
if(browser.isGecko) {
Element.addClassName(Element.getChildElement(send_params["target_el"]), "collapse_separate");
setTimeout(function(){
Element.removeClassName(Element.getChildElement(this), "collapse_separate");
}.bind(send_params["target_el"]), 100);
}
}.bind(this);
if(!inside_flag) {
var popup_url = commonCls.cutParamByUrl(commonCls.getUrl(id)).parseQuery();
if(popup_url['action'] == "pages_action_grouping") {
popup_url['action'] = "pages_view_grouping";
}
popup_url['blocktheme_name'] = theme_name;
popup_url['active_tab'] = 0;
var top_el = $(id);
var theme_params = new Object();
theme_params["method"] = "get";
theme_params["param"] = popup_url;
theme_params["top_el"] = top_el;
theme_params["target_el"] = top_el.parentNode;
theme_params["loading_el"] = top_el;
theme_params["callbackfunc"] =  function(){
url['theme_name'] = theme_name;
commonCls.sendView(parent_id, url, send_params, true);
}.bind(this);
commonCls.send(theme_params);
} else {
commonCls.sendView(parent_id, url, send_params, true);
}
},
defaultColorClick: function(id, theme_name) {
var top_el = $(id);
this.refresh_flag[id] = false;
var defultcolor_params = new Object();
defultcolor_params["method"] = "post";
defultcolor_params["param"] = {"action":"dialog_blockstyle_action_admin_setdefault","theme_name":theme_name};
defultcolor_params["top_el"] = $(id);
defultcolor_params["callbackfunc"] =  function(){
location.reload();
}.bind(this);
commonCls.send(defultcolor_params);
},
delStyleDef: function(theme_name) {
location.href = decodeURIComponent(_nc_current_url).replace("&amp;","&");
}
}
blockstyleCls = new clsBlockstyle();
var clsPagestyle = Class.create();
clsPagestyle.prototype = {
initialize: function() {
this.id = null;
this.top_el = null;
this.lang_down_arrow = null;
this.lang_right_arrow = null;
this.lang_cancel_confirm = null;
this.themefields = null;
this.theme_name = null;
this.header_flag = null;
this.leftcolumn_flag = null;
this.rightcolumn_flag = null;
this.header_el = null;
this.header_id_el = null;
this.leftcolumn_el = null;
this.centercolumn_el = null;
this.rightcolumn_el = null;
this.footer_el = null;
this.colorclick_flag = false;
this.chg_flag = false;
this.initColorFlag = false;
this.initStr = "";
this.tabset = null;
},
init: function(id, page_id, theme_name, header_flag, leftcolumn_flag, rightcolumn_flag, active_tab, change_flag, lang_cancel_confirm, lang_style, lang_theme, lang_layout, lang_coloration, lang_down_arrow, lang_right_arrow, pages_action, permalink_prohibition, permalink_prohibition_replace) {
this.id = id;
this.page_id = page_id;
this.theme_name = theme_name;
this.header_flag = header_flag;
this.leftcolumn_flag = leftcolumn_flag;
this.rightcolumn_flag = rightcolumn_flag;
this.pages_action = pages_action;
this.permalink_prohibition = permalink_prohibition;
this.permalink_prohibition_replace = permalink_prohibition_replace;
var top_el = $(id);
this.top_el = top_el;
this.header_id_el = $("__headercolumn");
this.header_el = $("_headercolumn");
if(this.header_el) {
this.header_add_module_el = Element.getChildElementByClassName(this.header_el, "headercolumn_addmodule");
}
this.leftcolumn_el = $("_leftcolumn");
this.centercolumn_el = $("_centercolumn");
this.rightcolumn_el =$("_rightcolumn");
this.footer_el = $("_footercolumn");
tabset = new compTabset(top_el);
tabset.addTabset(lang_theme, null);
tabset.addTabset(lang_style,pagestyleCls.clkStyle.bind($(id)));
tabset.addTabset(lang_layout);
tabset.addTabset(lang_coloration, null, pagestyleCls.clkColor.bind(this));
tabset.setActiveIndex(valueParseInt(active_tab));
tabset.render();
if(change_flag != "") {
this.chg_flag = true;
} else {
this.chg_flag = false;
}
this.tabset = tabset;
this.lang_cancel_confirm = lang_cancel_confirm;
this.lang_down_arrow = lang_down_arrow;
this.lang_right_arrow = lang_right_arrow;
},
clkStyle: function() {
var top_el = this;
var form = top_el.getElementsByTagName("form")[0];
commonCls.focus(form.page_name);
},
clkColor: function() {
this.initColorFlag = true;
this.initStr = "";
var coloration_el =$("_pagestyle_color");
if(coloration_el != null) {
this.setHighlightColor(document.body, "_pagestyle_body","backgroundColor");
this.setHighlightColor(this.header_el, "_pagestyle_headercolumn","backgroundColor");
this.setHighlightColor(this.leftcolumn_el, "_pagestyle_leftcolumn","backgroundColor");
this.setHighlightColor(this.centercolumn_el, "_pagestyle_centercolumn","backgroundColor");
this.setHighlightColor(this.rightcolumn_el, "_pagestyle_rightcolumn","backgroundColor");
this.setHighlightColor(this.footer_el, "_pagestyle_footercolumn","backgroundColor");
this.setHighlightColor(this.header_el, "_pagestyle_headercolumn_border_top_color","borderTopColor");
this.setHighlightColor(this.header_el, "_pagestyle_headercolumn_border_right_color","borderRightColor");
this.setHighlightColor(this.header_el, "_pagestyle_headercolumn_border_bottom_color","borderBottomColor");
this.setHighlightColor(this.header_el, "_pagestyle_headercolumn_border_left_color","borderLeftColor");
this.setHighlightColor(this.leftcolumn_el, "_pagestyle_leftcolumn_border_top_color","borderTopColor");
this.setHighlightColor(this.leftcolumn_el, "_pagestyle_leftcolumn_border_right_color","borderRightColor");
this.setHighlightColor(this.leftcolumn_el, "_pagestyle_leftcolumn_border_bottom_color","borderBottomColor");
this.setHighlightColor(this.leftcolumn_el, "_pagestyle_leftcolumn_border_left_color","borderLeftColor");
this.setHighlightColor(this.centercolumn_el, "_pagestyle_centercolumn_border_top_color","borderTopColor");
this.setHighlightColor(this.centercolumn_el, "_pagestyle_centercolumn_border_right_color","borderRightColor");
this.setHighlightColor(this.centercolumn_el, "_pagestyle_centercolumn_border_bottom_color","borderBottomColor");
this.setHighlightColor(this.centercolumn_el, "_pagestyle_centercolumn_border_left_color","borderLeftColor");
this.setHighlightColor(this.rightcolumn_el, "_pagestyle_rightcolumn_border_top_color","borderTopColor");
this.setHighlightColor(this.rightcolumn_el, "_pagestyle_rightcolumn_border_right_color","borderRightColor");
this.setHighlightColor(this.rightcolumn_el, "_pagestyle_rightcolumn_border_bottom_color","borderBottomColor");
this.setHighlightColor(this.rightcolumn_el, "_pagestyle_rightcolumn_border_left_color","borderLeftColor");
this.setHighlightColor(this.footer_el, "_pagestyle_footercolumn_border_top_color","borderTopColor");
this.setHighlightColor(this.footer_el, "_pagestyle_footercolumn_border_right_color","borderRightColor");
this.setHighlightColor(this.footer_el, "_pagestyle_footercolumn_border_bottom_color","borderBottomColor");
this.setHighlightColor(this.footer_el, "_pagestyle_footercolumn_border_left_color","borderLeftColor");
}
this.initColorFlag = false;
if(this.initStr != "") {
var color_params = new Object();
color_params["method"] = "post";
color_params["param"] = "dialog_pagestyle_action_edit_change&page_id="+this.page_id+"&_pagestyle_flag=1"+this.initStr;
commonCls.send(color_params);
}
},
setHighlightColor: function(el, child_class_name, property_name) {
if(el) {
var bgImageStyle = "none";
if(property_name == "backgroundColor" || property_name == "background-color") {
bgImageStyle = Element.getStyle(el, "backgroundImage");
}
var color = commonCls.getColorCode(el,property_name);
var column_el = $(child_class_name);
var count = 0;
for (var i = 0,column_len = column_el.childNodes.length; i < column_len; i++) {
var child_el = column_el.childNodes[i];
if(child_el.nodeType == 1) {
if(child_el.tagName.toLowerCase() == "a" && child_el.title == color && bgImageStyle == "none") {
Element.addClassName(child_el, "highlight");
child_el.onclick();
break;
} else if(child_el.tagName.toLowerCase() == "select") {
if(bgImageStyle != "none") {
var selected_flag = false;
var select_el = child_el;
if(bgImageStyle.match("^url[(]{1}\"")) {
var repBgImageStyle = bgImageStyle.replace(_nc_base_url, "").replace("../", "").replace("url(\"", "").replace("\")", "");
} else {
var repBgImageStyle = bgImageStyle.replace(_nc_base_url, "").replace("../", "").replace("url(", "").replace(")", "");
}
for (var j = 0, option_len = select_el.childNodes.length; j < option_len; j++) {
var option_el = select_el.childNodes[j];
if(option_el.value.match(repBgImageStyle)) {
option_el.selected = "selected";
selected_flag = true;
select_el.onchange();
break;
}
}
}
} else if(child_el.tagName.toLowerCase() == "input" && child_el.type != "hidden") {
if(color != "transparent") {
child_el.value = color;
child_el.onchange();
}
}
count++;
}
}
} else {
commonCls.displayNone($(child_class_name));
var sub_el = $(child_class_name + "_border");
if(sub_el) {
commonCls.displayNone(sub_el);
}
}
},
displayChange: function(this_el, el) {
var img_el = Element.getChildElement(this_el);
if(img_el.src.match("down_arrow.gif")) {
img_el.src = img_el.src.replace("down_arrow.gif","right_arrow.gif");
img_el.alt = this.lang_right_arrow;
this_el.title = this.lang_right_arrow;
} else {
img_el.src = img_el.src.replace("right_arrow.gif","down_arrow.gif");
img_el.alt = this.lang_down_arrow;
this_el.title = this.lang_down_arrow;
}
if(el == null || el == undefined) {
var next_el = this_el.nextSibling;
if(Element.hasClassName(next_el,"_blockstyle_custom_sample")) {
next_el = next_el.nextSibling;
}
commonCls.displayChange(next_el);
} else {
commonCls.displayChange($(el));
}
},
themeClick: function(this_el, theme_name) {
if(!this.themefields) {
var pagestyle_top = $("_pagestyle_top");
this.themefields = Element.getElementsByClassName(pagestyle_top, "_pagestyle");
}
var return_flag = false;
this.themefields.each(function(field) {
if(Element.hasClassName(field,"highlight")) {
if(field == this_el) {
return_flag = true;
return;
} else {
Element.removeClassName(field,"highlight");
}
}
}.bind(this));
if(return_flag) {
return;
}
Element.addClassName(this_el,"highlight");
var all_apply = 0;
var defultcolor_params = new Object();
defultcolor_params["method"] = "post";
defultcolor_params["param"] = {"action":"dialog_pagestyle_action_edit_setdefault","page_id":this.page_id,"_pagestyle_flag":1,"all_apply":all_apply,"sesson_only":1};
defultcolor_params["callbackfunc"] =  function(){
var theme_params = new Object();
theme_params["method"] = "post";
theme_params["param"] = {"action":"dialog_pagestyle_action_edit_change","page_id":this.page_id,"_pagestyle_flag":1,"theme_name": theme_name};
theme_params["callbackfunc"] =  function(){
this.refresh(0);
}.bind(this);
commonCls.send(theme_params);
}.bind(this);
commonCls.send(defultcolor_params);
},
colorClick: function(class_name, property_name, color, this_el) {
if(this_el.tagName.toLowerCase() != "select" && (color == "" || color =="1px solid " || this.colorclick_flag == true || (color.length == 7 && color.indexOf('#') != 0))) {
return;
}
if(property_name == "backgroundColor" && color == null) color = this_el.title;
else if(color == null && this_el.title == "transparent")  color = "0px none";
else if(color == null) color = "1px solid " + this_el.title;
if(typeof class_name == "string") {
var el = Element.getChildElementByClassName(document.body, class_name);
var send_name = class_name+"_"+property_name;
} else {
var el = class_name;
var send_name = "body"+"_"+property_name;
}
if(!this.initColorFlag) {
this.colorclick_flag = true;
if(property_name == "backgroundColor") {
el.style.background = "none" ;
}
if(property_name == "background" && color == "") {
color = "none";
}
eval("el.style."+property_name + "=\"" + color+"\";");
if(!(browser.isIE) && property_name.match("border")) {
$("_container").style.display = "none";
}
var color_params = new Object();
color_params["method"] = "post";
color_params["param"] = "dialog_pagestyle_action_edit_change&page_id="+this.page_id+"&_pagestyle_flag=1&"+send_name+"="+encodeURIComponent(color);
color_params["callbackfunc"] =  function(){
this.colorclick_flag = false;
if(!(browser.isIE) && property_name.match("border") ) {
$("_container").style.display = "table";
}
}.bind(this);
commonCls.send(color_params);
this.setHighlight(this_el);
this.chg_flag = true;
} else {
this.initStr += "&"+send_name+"="+encodeURIComponent(color);
}
},
setHighlight: function(this_el) {
for (var i = 0; i < this_el.parentNode.childNodes.length; i++) {
var child_el = this_el.parentNode.childNodes[i];
if(child_el.nodeType != 1) continue;
if(Element.hasClassName(child_el,"highlight")) {
Element.removeClassName(child_el,"highlight");
} else if(this_el.tagName.toLowerCase() != "select" && child_el.tagName.toLowerCase() == "select") {
child_el.selectedIndex = 0;
} else if(this_el.tagName.toLowerCase() != "input" && child_el.tagName.toLowerCase() == "input" && child_el.type != "hidden") {
child_el.value = "";
}
}
if(this_el.tagName.toLowerCase() != "input" && this_el.tagName.toLowerCase() != "select") {
Element.addClassName(this_el,"highlight");
}
},
defaultColorClick: function() {
var top_el = $(this.id);
var form = top_el.getElementsByTagName("form")[0];
if(form.pagestyle_all_apply) {
if(form.pagestyle_all_apply.checked) {
var all_apply = 1;
} else {
var all_apply = 0;
}
} else {
var all_apply = 0;
}
var defultcolor_params = new Object();
defultcolor_params["method"] = "post";
defultcolor_params["param"] = {"action":"dialog_pagestyle_action_edit_setdefault","page_id":this.page_id,"_pagestyle_flag":1,"all_apply":all_apply};
defultcolor_params["callbackfunc"] =  function(){
this.refresh(3);
}.bind(this);
commonCls.send(defultcolor_params);
},
layoutClick: function(el , header_flag, leftcolum_flag, rightcolumn_flag) {
if(Element.hasClassName(el,"highlight")) {
return;
}
this.chg_flag = true;
var pagestyle_layout_el = Element.getParentElementByClassName(el, "_pagestyle_layout");
var highlight_el = Element.getChildElementByClassName(pagestyle_layout_el, "highlight");
Element.removeClassName(highlight_el,"highlight");
Element.addClassName(el,"highlight");
var refresh_flag = false;
if(header_flag) {
if(this.header_id_el) {
if(this.header_add_module_el) commonCls.displayVisible(this.header_add_module_el);
commonCls.displayVisible(this.header_id_el);
} else {
refresh_flag = true;
}
this.header_flag = 1;
} else {
if(this.header_add_module_el) commonCls.displayNone(this.header_add_module_el);
if(this.header_id_el)  commonCls.displayNone(this.header_id_el);
this.header_flag = 0;
}
var colspan = 1;
if(leftcolum_flag) {
colspan++;
if(this.leftcolumn_el) {
commonCls.displayVisible(this.leftcolumn_el);
} else {
refresh_flag = true;
}
this.leftcolumn_flag = 1;
} else {
if(this.leftcolumn_el) commonCls.displayNone(this.leftcolumn_el);
this.leftcolumn_flag = 0;
}
if(rightcolumn_flag) {
colspan++;
if(this.rightcolumn_el) {
commonCls.displayVisible(this.rightcolumn_el);
} else {
refresh_flag = true;
}
this.rightcolumn_flag = 1;
} else {
if(this.rightcolumn_el) commonCls.displayNone(this.rightcolumn_el);
this.rightcolumn_flag = 0;
}
if(this.header_id_el) {
this.header_id_el.colspan = colspan;
}
if(this.footer_el) {
this.footer_el.colspan = colspan;
}
var layout_params = new Object();
layout_params["method"] = "post";
layout_params["param"] = {"action":"dialog_pagestyle_action_edit_change","_pagestyle_flag":1,"page_id":this.page_id,"header_flag": this.header_flag,"leftcolumn_flag": this.leftcolumn_flag,"rightcolumn_flag": this.rightcolumn_flag};
layout_params["callbackfunc"] =  function(){
if(refresh_flag != "" || !(browser.isIE)) {
this.refresh(2);
}
}.bind(this);
commonCls.send(layout_params);
},
refresh: function(active_tab, append_str) {
var top_el = $(this.id);
append_str = (append_str == undefined || append_str == null) ? "" : append_str;
var str = "&_layoutmode=off";
if(active_tab != undefined && active_tab != null) {
str += "&active_tab="+active_tab;
}
location.href = _nc_base_url + _nc_index_file_name + "?action="+ this.pages_action+"&page_id="+this.page_id+
"&_pagestyle_flag=1&pagestyle_x="+top_el.parentNode.style.left+"&pagestyle_y="+top_el.parentNode.style.top+
str + append_str;
},
chgGeneral: function(el) {
var top_el = $(this.id);
var form = top_el.getElementsByTagName("form")[0];
var name = el.name;
var hidden_el = el.nextSibling;
if(hidden_el.value == el.value) {
return;
}
this.chg_flag = true;
var container_el = $("_container");
if(name == "align") {
if(el.value == "center") {
form.leftmargin.value = "0";
form.rightmargin.value = "0";
container_el.style.marginRight = "";
container_el.style.marginLeft = "";
var buf_hidden_el = form.leftmargin.nextSibling;
buf_hidden_el.value = "0";
var buf_hidden_el = form.rightmargin.nextSibling;
buf_hidden_el.value = "0";
}
container_el.align = el.value;
container_el.style.textAlign = el.value;
} else if(name == "topmargin") {
container_el.style.marginTop = el.value + "px";
} else if(name == "rightmargin") {
container_el.style.marginRight = el.value + "px";
} else if(name == "bottommargin") {
container_el.style.marginBottom = el.value + "px";
} else if(name == "leftmargin") {
container_el.style.marginLeft = el.value + "px";
}
if((name == "leftmargin" || name == "rightmargin") && el.value != "0" && form.align.value == "center") {
form.align.value = "left";
container_el.align = "left";
container_el.style.textAlign = "left";
var buf_hidden_el = form.align.nextSibling;
buf_hidden_el.value = "left";
}
if(name == "page_name") {
var robj = new RegExp(this.permalink_prohibition, "ig");
if(hidden_el.value.replace(robj, this.permalink_prohibition_replace) == form.permalink.value.replace(robj, this.permalink_prohibition_replace)) {
form.permalink.value = el.value.replace(robj, this.permalink_prohibition_replace);
}
}
hidden_el.value = el.value;
var general_params = new Object();
general_params["method"] = "post";
general_params["param"] = {"action":"dialog_pagestyle_action_edit_change","_pagestyle_flag":1,"page_id":this.page_id};
general_params["param"][name] = el.value;
commonCls.send(general_params);
},
okClick: function(id) {
var top_el = $(this.id);
var form = top_el.getElementsByTagName("form")[0];
if(form.pagestyle_all_apply) {
if(form.pagestyle_all_apply.checked) {
var all_apply = 1;
} else {
var all_apply = 0;
}
} else {
var all_apply = 0;
}
if(this.chg_flag) {
setTimeout(function() {
var top_el = $(this.id);
var theme_params = new Object();
if(typeof form.titletag != "undefined") {
var titletag = form.titletag.value;
} else {
var titletag = null;
}
if(typeof form.permalink != "undefined") {
var permalink = form.permalink.value;
} else {
var permalink = null;
}
theme_params["method"] = "post";
theme_params["top_el"] = $(this.id);
theme_params["param"] = {"action":"dialog_pagestyle_action_edit_init","page_id":this.page_id,"page_name":form.page_name.value,"permalink":permalink,"titletag":titletag,"meta_description":form.meta_description.value,"meta_keywords":form.meta_keywords.value,"_pagestyle_flag":1,"all_apply":all_apply,"prefix_id_name":"dialog_pagestyle"};
theme_params["callbackfunc"] =  function(res){
if(form.permalink_url && form.permalink.type != "hidden" && form.permalink.value != '') {
location.href = form.permalink_url.value + form.permalink.value + '/';
} else {
location.href = _nc_base_url + _nc_index_file_name + "?action=" + this.pages_action + "&page_id="+this.page_id;
}
}.bind(this);
theme_params["callbackfunc_error"] =  function(res){
commonCls.alert(res);
this.tabset.setActiveIndex(1);
this.tabset.refresh();
}.bind(this);
commonCls.send(theme_params);
}.bind(this),300);
} else {
commonCls.removeBlock(id);
}
},
cancelClick: function(id) {
if(this.chg_flag) {
if(!commonCls.confirm(this.lang_cancel_confirm))return false;
location.href = _nc_base_url + _nc_index_file_name + "?action=pages_view_main&page_id="+this.page_id;
} else {
commonCls.removeBlock(id);
}
}
}
pagestyleCls = new clsPagestyle();
var clsUserinf = Class.create();
var userinfCls = Array();
clsUserinf.prototype = {
initialize: function(id, user_id, prefix_id_name) {
this.id = id;
this.user_id = user_id;
this.prefix_id_name = prefix_id_name;
this.inEmailReception = new Object();
this.inItems = new Object();
this.inPublic = new Object();
this.inUpdItems = new Object();
},
init:function() {
var top_el = $(this.id);
var focus_el = Element.getChildElementByClassName(top_el,"userinf_edit_item_label");
commonCls.focus(focus_el);
},
clkLabel: function(this_el) {
var edit_el = this_el.nextSibling;
var a_el = Element.getChildElement(edit_el);
if(a_el && a_el.tagName.toLowerCase() == "a") {
a_el.onclick();
}
},
clkItems: function(this_el, item_id, type) {
var edit_el = this_el.nextSibling;
commonCls.displayNone(this_el);
commonCls.displayVisible(edit_el);
switch (type) {
case "text":
case "textarea":
case "email":
case "mobile_email":
var input_el = Element.getChildElement(edit_el);
input_el.focus();
input_el.select();
break;
case "file":
var input_el = Element.getChildElement(edit_el, 2);
input_el.focus();
input_el.select();
break;
case "password":
var input_el = edit_el.getElementsByTagName("input")[0];
input_el.focus();
input_el.select();
break;
case "select":
case "radio":
case "checkbox":
var form_el = edit_el.getElementsByTagName("form")[0];
if(type == "select") {
var input_els = Form.getElements(form_el);
} else {
var input_els = Form.getInputs(form_el, type);
}
input_els[0].focus();
break;
}
},
focusReception: function(item_id, focus_flag) {
this.inEmailReception[item_id] = focus_flag;
},
focusPublic: function(item_id, focus_flag) {
this.inPublic[item_id] = focus_flag;
},
focusItem: function(item_id, focus_flag) {
this.inItems[item_id] = focus_flag;
},
updItems: function(event_type, this_el, item_id, type, focus_flag) {
var input_type = this_el.type;
var reception_el = $("userinf_items_reception" + this.id + "_" + item_id);
var public_el = $("userinf_items_public" + this.id + "_" + item_id);
if(input_type == "text" && event_type == "keypress") {
this_el.blur();
}
if((reception_el && !Element.hasClassName(reception_el.parentNode,"display-none")) ||
(public_el && !Element.hasClassName(public_el.parentNode,"display-none"))) {
if(focus_flag == undefined) {
setTimeout(function(){this.updItems(event_type, this_el, item_id, type, true)}.bindAsEventListener(this), 500);
return;
} else {
if(this.inItems[item_id] === true || this.inEmailReception[item_id] === true || this.inPublic[item_id] === true) {
setTimeout(function(){this.updItems(event_type, this_el, item_id, type, true)}.bindAsEventListener(this), 500);
return;
}
}
} else if(type == "radio" && event_type == "blur" && focus_flag == undefined) {
return;
}
var top_el = $(this.id);
if(top_el == null) return;
var edit_el = Element.getParentElementByClassName(this_el,"userinf_edit_item");
var label_el = edit_el.previousSibling;
var select_flag = false;
var upd_params = new Object();
if(type != "file") {
upd_params['action'] = "userinf_action_main_init";
} else {
upd_params['action'] = "userinf_action_main_upload_image";
}
upd_params['item_id'] = item_id;
upd_params['user_id'] = this.user_id;
if(this.inUpdItems[upd_params['item_id']] == true) {
return;
}
this.inUpdItems[upd_params['item_id']] = true;
switch (type) {
case "text":
case "email":
case "mobile_email":
upd_params['content'] = this_el.value;
if(reception_el) {
upd_params['email_reception_flag'] = (reception_el.checked) ? 1 : 0;
} else {
if(type == "text") {
upd_params['email_reception_flag'] = 0;
} else {
upd_params['email_reception_flag'] = 1;
}
}
select_flag = true;
break;
case "textarea":
var textarea_el = $("userinf_items" + this.id + "_" + upd_params['item_id']);
upd_params['content'] = textarea_el.value;
this_el = textarea_el;
select_flag = true;
break;
case "password":
var current_el = $("userinf_items_current" + this.id + "_" + upd_params['item_id']);
var new_el = $("userinf_items_new" + this.id + "_"  + upd_params['item_id']);
var comfirm_el = $("userinf_items_confirm" + this.id + "_" + upd_params['item_id']);
upd_params['content'] = new_el.value;
if(comfirm_el) upd_params['confirm_content'] = comfirm_el.value;
if(current_el) {
upd_params['current_content'] = current_el.value;
this_el = current_el;
} else {
this_el = new_el;
}
select_flag = true;
break;
case "file":
upd_params['unique_id'] = this.user_id;
break;
case "select":
case "radio":
case "checkbox":
var form_el = $("userinf_form" + this.id + "_" + item_id);
if(type == "select") {
var input_els = Form.getElements(form_el);
} else {
var input_els = Form.getInputs(form_el, type);
}
var value_lang = "";
upd_params['content'] = "";
for (var i = 0, length = input_els.length; i < length; i++) {
if(i == 0) {
this_el = input_els[i];
}
var value = Form.Element.getValue(input_els[i]);
if(type == "select") {
var checked_flag = true;
} else {
var checked_flag = input_els[i].checked;
}
if(value != null && checked_flag) {
var value_arr = value.split("|");
if(value_arr[1]) {
upd_params['content'] += value_arr[0] + "|";
value_lang += value_arr[1] + "|";
} else {
upd_params['content'] += value + "|";
}
}
}
break;
}
if(public_el) {
upd_params['public_flag'] = (public_el.checked) ? 1 : 0;
} else {
upd_params['public_flag'] = 1;
}
upd_params['prefix_id_name'] = this.prefix_id_name;
var send_param = new Object();
send_param["method"] = "post";
send_param["param"] = upd_params;
send_param["top_el"] = top_el;
send_param["callbackfunc"] = function(file_list, res){
if(type == "text" || type == "email" || type == "mobile_email" || type == "textarea" ||
type == "select" || type == "radio" || type == "checkbox") {
upd_params['content'] = upd_params['content'].escapeHTML();
}
if(upd_params['content'] == "") upd_params['content'] = "&nbsp;";
switch (type) {
case "text":
case "email":
case "mobile_email":
label_el.innerHTML = upd_params['content'];
break;
case "textarea":
var re_cut = new RegExp("\n", "g");
label_el.innerHTML = upd_params['content'].replace(re_cut, "<br />");
break;
case "password":
if(current_el) current_el.value = upd_params['content'];
if(comfirm_el) comfirm_el.value = "";
new_el.value = "";
break;
case "file":
var url = "?action="+ file_list[0]['action_name'] + "&upload_id=" + file_list[0]['upload_id'];
var img_el = Element.getChildElement(label_el);
img_el.src = url;
commonCls.displayVisible(img_el);
break;
case "select":
case "radio":
case "checkbox":
var re_sep = new RegExp("\\|", "g");
if(value_lang != "") {
var content_str = value_lang.replace(re_sep,",").substr(0,value_lang.length - 1);
} else {
var content_str = upd_params['content'].replace(re_sep,",").substr(0,upd_params['content'].length - 1);
}
label_el.innerHTML =content_str;
break;
}
commonCls.displayNone(edit_el);
commonCls.displayVisible(label_el);
if(input_type == "radio" || input_type == "select-one"
|| input_type == "button") {
label_el.focus();
}
this.inUpdItems[upd_params['item_id']] = false;
}.bind(this);
if(type == "file") {
send_param["param"]['unique_id'] = this.user_id;
send_param['form_prefix'] = "userinf_attachment_"+ upd_params['item_id'];
send_param["callbackfunc_error"] = function(file_list, res){
commonCls.alert(res);
this_el.focus();
if(select_flag) this_el.select();
this.inUpdItems[upd_params['item_id']] = false;
}.bind(this);
commonCls.sendAttachment(send_param);
} else {
send_param["callbackfunc_error"] = function(res){
commonCls.alert(res);
this_el.focus();
if(select_flag) this_el.select();
this.inUpdItems[upd_params['item_id']] = false;
}.bind(this);
commonCls.send(send_param);
}
},
cancelItems: function(event, this_el, type) {
var edit_el = Element.getParentElementByClassName(this_el,"userinf_edit_item");
var label_el = edit_el.previousSibling;
commonCls.displayNone(edit_el);
commonCls.displayVisible(label_el);
label_el.focus();
},
delImage: function(event, this_el, type) {
var edit_el = Element.getParentElementByClassName(this_el,"userinf_edit_item");
var label_el = edit_el.previousSibling;
var img_el = Element.getChildElement(label_el);
Element.addClassName(img_el, "display-none");
img_el.src = "";
commonCls.displayNone(edit_el);
commonCls.displayVisible(label_el);
label_el.focus();
},
initRoom: function(count, visible_rows) {
var opts = null;
new compLiveGrid (this.id, visible_rows, count, null, opts);
},
initMonthly: function() {
var offset = 400;
var monthlynumber_list_el = $("monthlynumber_list" + this.id);
var height = monthlynumber_list_el.offsetHeight;
if(height >= offset) {
Element.setStyle(monthlynumber_list_el, {overflow:"auto"});
Element.setStyle(monthlynumber_list_el, {height:offset+"px"});
if(!browser.isIE) {
Element.setStyle(monthlynumber_list_el, {width:monthlynumber_list_el.offsetWidth+"px"});
}
var parent_el = monthlynumber_list_el.parentNode;
if(browser.isIE) {
Element.setStyle(parent_el, {"padding-right":"20px"});
}
}
},
initModulesinfo: function() {
var top_el = $(this.id);
var tabset = new compTabset(top_el);
tabset.render();
},
withdrawAccept: function(this_el) {
var next_btn_el = $("userinf_next" + this.id);
if(this_el.checked) {
next_btn_el.disabled = false;
} else {
next_btn_el.disabled = true;
}
}
}
var clsMenu = Class.create();
var menuCls = Array();
clsMenu.prototype = {
initialize: function(id) {
this.id = id;
this.form = null;
this.menuLodingFlag = true;
this.url = null;
this.center_flag = false;
this.dndMgrMenuObj = new Object();
this.dndCustomDrag = null;
this.dndCustomDropzone = null;
this.margin_left = 20;
this.inRenameFlag = false;
this.flat_flag = false;
},
menuMainInit: function() {
},
menuEditInit: function(center_flag,margin_left, flat_flag) {
var top_el = $(this.id);
this.flat_flag = flat_flag;
this.form = top_el.getElementsByTagName("form")[0];
var input = this.form.page_name;
setTimeout(this.menuRenameFocus.bind(this),0);
this._editObserver(input);
this.center_flag = center_flag;
this.margin_left = margin_left;
this.dndCustomDrag = Class.create();
this.dndCustomDrag.prototype = Object.extend((new compDraggable), {
prestartDrag: function()
{
var draggable = this.htmlElement;
var next_el = draggable.nextSibling;
if(next_el && next_el.id.match("_menu_")) {
Element.addClassName(next_el,"display-none");
}
},
endDrag: function() {
var draggable = this.htmlElement;
Element.setStyle(draggable, {opacity:""});
var drag_params = this.getParams();
var parent_el = $(drag_params[1]);
if(parent_el && parent_el.innerHTML=="") {
Element.remove(parent_el);
}
}
});
this.dndCustomDropzone = Class.create();
this.dndCustomDropzone.prototype = Object.extend((new compDropzone), {
canAccept: function(draggableObjects) {
var theGUI = draggableObjects[0].getDroppedGUI();
var htmlElement = this.getHTMLElement();
var id_arr = theGUI.id.split("_");
var parent_id = id_arr[3];
if((parent_id == "1" && Element.hasClassName(theGUI, "_menu_sub_group")) && valueParseInt(theGUI.style.marginLeft) != valueParseInt(htmlElement.style.marginLeft)) {
return false;
}
return true;
},
showHover: function(event) {
var htmlElement = this.getHTMLElement();
var id_arr = htmlElement.id.split("_");
var room_id = id_arr[3];
var subgroup_flag = false;
var current_drop_el = Element.getParentElementByClassName(Event.element(event), "menu_row_top");
if(Element.hasClassName(current_drop_el, "_menu_sub_group")) {
subgroup_flag = true;
}
var child_el = Element.getChildElement(htmlElement);
var node_type_el = Element.getChildElementByClassName(htmlElement,"_menu_node_type");
if(Element.hasClassName(node_type_el, "menu_node") && room_id != "top" && room_id != "group" && subgroup_flag != true) {
if ( this._showHover(child_el) )
return;
var offset = Position.cumulativeOffset(htmlElement);
var ex = offset[0];
var ey = offset[1];
var part_height = (htmlElement.offsetHeight/5);
var y = Event.pointerY(event);
if(y < ey + part_height * 2) {
this.ChgSeqPosition = "top";
var top_px = ey  + "px";
} else if(y > ey + part_height * 3) {
this.ChgSeqPosition = "bottom";
var top_px = (ey + htmlElement.offsetHeight)  + "px";
} else {
this.ChgSeqPosition = "inside";
if(this.ChgSeqHover) {
Element.remove(this.ChgSeqHover);
this.ChgSeqHover = null;
}
child_el.style.backgroundColor = "#ffff99";
}
if(this.ChgSeqPosition !=  "inside") {
if(this.ChgSeqHover == undefined || this.ChgSeqHover == null) {
this._hideHover(child_el);
this.ChgSeqHover = document.createElement("DIV");
}
document.body.appendChild(this.ChgSeqHover);
this.ChgSeqHover.style.width = htmlElement.offsetWidth + "px";
this.ChgSeqHover.style.height = "1px";
this.ChgSeqHover.style.position = "absolute";
this.ChgSeqHover.style.left = ex  + "px";
this.ChgSeqHover.style.top = top_px;
this.ChgSeqHover.style.borderTop = "3px";
this.ChgSeqHover.style.borderTopStyle = "solid";
this.ChgSeqHover.style.borderTopColor = "#ffff00";
}
} else {
this.showChgSeqHover(event);
}
},
hideHover: function(event)
{
var htmlElement = this.getHTMLElement();
var child_el = Element.getChildElement(htmlElement);
if ( this._hideHover(child_el) )
return;
if(this.ChgSeqHover) {
Element.remove(this.ChgSeqHover);
this.ChgSeqHover = null;
}
},
accept: function(draggableObjects)
{
var params = this.getParams();
var top_id = params[0];
var htmlElement = this.getHTMLElement();
if ( htmlElement == null )
return;
var n = draggableObjects.length;
for ( var i = 0 ; i < n ; i++ ) {
var theGUI = draggableObjects[i].getDroppedGUI();
if ( Element.getStyle( theGUI, "position" ) == "absolute" ) {
theGUI.style.position = "static";
theGUI.style.top = "";
theGUI.style.left = "";
}
var margin_left = params[1];
var id_arr = theGUI.id.split("_");
var page_id = id_arr[2];
if(this.ChgSeqPosition == "inside") {
var margin_px = (valueParseInt(htmlElement.style.marginLeft) + margin_left);
} else {
var margin_px = valueParseInt(htmlElement.style.marginLeft);
}
theGUI.style.marginLeft = margin_px + "px";
var doc = document.createDocumentFragment();
doc.appendChild(theGUI);
var child_el = $("_menu_"+page_id+top_id);
if(child_el) {
var child_parent_el = child_el.parentNode;
this._setMargin(child_el, margin_px, margin_left);
doc.appendChild(child_el);
if(child_parent_el && child_parent_el.innerHTML=="") {
Element.remove(child_parent_el);
}
}
if(this.ChgSeqPosition == "top") {
htmlElement.parentNode.insertBefore(doc, htmlElement);
} else if(this.ChgSeqPosition == "bottom"){
var next_el = htmlElement.nextSibling;
if(next_el && next_el.id.match("_menu_")) {
next_el = next_el.nextSibling;
}
if(!next_el) {
htmlElement.parentNode.appendChild(doc);
} else {
next_el.parentNode.insertBefore(doc, next_el);
}
} else {
var next_el = htmlElement.nextSibling;
if(next_el && next_el.id.match("_menu_")) {
next_el.appendChild(doc);
if(next_el.innerHTML != "") {
Element.removeClassName(next_el,"display-none");
}
} else {
var id_arr = htmlElement.id.split("_");
var page_id = id_arr[2];
var div = document.createElement("DIV");
div.id = "_menu_"+page_id+top_id;
div.className = "_menu_"+page_id+top_id;
var next_el = htmlElement.nextSibling;
if(!next_el) {
htmlElement.parentNode.appendChild(div);
} else {
next_el.parentNode.insertBefore(div, next_el);
}
div.appendChild(doc);
}
}
}
},
_setMargin: function(child_el, margin_px, margin_left) {
if(child_el) {
for (var i = 0,child_len=child_el.childNodes.length; i < child_len; i++) {
if(!child_el.childNodes[i].id.match("_menu_")) {
child_el.childNodes[i].style.marginLeft = (margin_px + margin_left) + "px";
} else {
this._setMargin(child_el.childNodes[i], margin_px, margin_px + margin_left);
}
}
}
},
save: function(draggableObjects) {
if(this.ChgSeqPosition == null) {
return false;
}
var params = this.getParams();
var id = params[0];
var top_el = $(id);
var drag_el = draggableObjects[0].getHTMLElement();
var id_arr = drag_el.id.split("_");
var drag_page_id = id_arr[2];
var htmlElement = this.getHTMLElement();
var id_arr = htmlElement.id.split("_");
var drop_page_id = id_arr[2];
var chgseq_params = new Object();
chgseq_params["param"] = {"action":"menu_action_edit_chgseq", "drag_page_id":drag_page_id,
"drop_page_id":drop_page_id, "position":this.ChgSeqPosition};
chgseq_params["callbackfunc_error"] = function(res){
commonCls.alert(res);
location.reload();
}.bind(this);
chgseq_params["method"] = "post";
chgseq_params["top_el"] = top_el;
commonCls.send(chgseq_params);
return true;
}
});
this.dndMgrMenuObj = new Object();
var range_el = $("_menu_range"+this.id);
var menu_rowfields = Element.getElementsByClassName(range_el, "menu_row_top");
menu_rowfields.each(function(menu_row_el) {
if(menu_row_el.id) {
this._dndObserver(menu_row_el);
}
}.bind(this));
},
menuRenameFocus: function(input) {
if(!input || input.tagName == undefined) {
var input = this.form.page_name;
}
if(input) {
input.focus();
input.select();
}
},
_dndObserver: function(el) {
var id_arr = el.id.split("_");
var room_id = id_arr[3];
var img_el = Element.getChildElementByClassName(el,"_menu_displayseq");
if(img_el != null) {
if(!this.dndMgrMenuObj[room_id]) {
this.dndMgrMenuObj[room_id] = new compDragAndDrop();
this.dndMgrMenuObj[room_id].registerDraggableRange(el.parentNode);
}
this.dndMgrMenuObj[room_id].registerDraggable(new this.dndCustomDrag(el, img_el.parentNode, new Array(this.id, el.parentNode.id)));
this.dndMgrMenuObj[room_id].registerDropZone(new this.dndCustomDropzone(el, new Array(this.id, this.margin_left)));
}
},
_editObserver: function(el) {
if(el) {
Event.observe(el, 'keydown', this.menuRename.bindAsEventListener(this, el) , true, $(this.id));
Event.observe(el, 'change',  this.menuRename.bindAsEventListener(this, el), true, $(this.id)) ;
}
},
menuRename: function(event, el) {
if (!this.inRenameFlag && (event.type == "change" || event.keyCode == 13)) {
var top_el = $(this.id);
var parent_el = Element.getParentElement(el, 2);
if(parent_el) {
this.inRenameFlag = true;
var idName = parent_el.id;
var page_id = parseInt(idName.replace("_menutop"+this.id+"_",""));
var rename_params = new Object();
rename_params["method"] = "post";
rename_params["param"] = {"action":"menu_action_edit_rename","main_page_id":page_id,"page_name": el.value};
rename_params["callbackfunc"] = function(res){
this.inRenameFlag = false;
if(el) {
if(res == "") {
var value = el.value;
} else {
var value = res;
}
Event.stopObserving(el, "keydown",this.menuRename.bindAsEventListener(this, el),false);
Event.stopObserving(el, "change",this.menuRename.bindAsEventListener(this, el),false);
var text = document.createTextNode(value);
var span_el = Element.getChildElement(parent_el);
span_el.innerHTML = "";
span_el.appendChild(text);
}
}.bind(this);
rename_params["callbackfunc_error"] = function(res){
this.inRenameFlag = false;
commonCls.alert(res);
}.bind(this);
rename_params["top_el"] = top_el;
commonCls.send(rename_params);
}
}
},
menuMainShow: function() {
commonCls.sendView(this.id,"menu_view_main_init");
},
menuEditShow: function() {
commonCls.sendView(this.id,"menu_view_edit_init");
},
menuNodeClick: function(event, page_id, edit_flag, addpage_flag) {
var class_name = "_menu_" + page_id+this.id;
var top_class_name = "_menutop_" + page_id;
var top_el = $(this.id);
var top_node_el = Element.getChildElementByClassName(top_el,top_class_name);
var el = Element.getChildElementByClassName(top_el,class_name);
if(edit_flag) {
var top_node_el_href = Element.getParentElementByClassName(top_node_el, "menu_row_top");
this.cancelActiveBtn();
this.addActiveBtn(top_node_el_href);
var child_el = Element.getChildElement(top_node_el);
var input_el = Element.getChildElement(child_el);
var node_type_el = Element.getChildElementByClassName(top_node_el_href, "_menu_node_type");
if(!input_el && !Element.hasClassName(child_el, "menu_lbl_disabled")) {
var input = document.createElement("INPUT");
input.type = "text";
input.className = "menu_pagename_text";
input.value = child_el.innerHTML.unescapeHTML();
child_el.innerHTML = "";
child_el.appendChild(input);
this.menuRenameFocus(input);
this._editObserver(input);
}
} else {
var top_node_el_href = top_node_el;
}
if(!el) {
var detail_params = new Object();
detail_params["method"] = "get";
if(edit_flag) {
var visibility_el = $("_menuvisibility"+this.id+"_" + page_id);
if(!visibility_el || visibility_el.src == undefined) {
var visibility_flag = 1;
} else if(visibility_el.src.match("off.gif")) {
var visibility_flag = 0;
} else {
var visibility_flag = 1;
}
if(this.flat_flag)
var flat_flag = 1;
else
var flat_flag = 0;
detail_params["param"] = {"action":"menu_view_edit_detail","main_page_id":page_id,"visibility_flag":visibility_flag,"flat_flag":flat_flag};
} else {
detail_params["param"] = {"action":"menu_view_main_detail","main_page_id":page_id};
}
detail_params["callbackfunc"] = function(res){
var div = document.createElement("DIV");
div.id = "_menu_"+page_id+this.id;
if(res == "") {
div.className = "_menu_"+page_id+this.id+" display-none";
} else {
div.className = "_menu_"+page_id+this.id;
}
var next_el = top_node_el_href.nextSibling;
if(!next_el) {
top_node_el_href.parentNode.appendChild(div);
} else {
next_el.parentNode.insertBefore(div, next_el);
}
div.innerHTML = res;
var menu_rowfields = Element.getElementsByClassName(div, "menu_row_top");
menu_rowfields.each(function(menu_row_el) {
if(menu_row_el.id) {
this._dndObserver(menu_row_el);
}
}.bind(this));
}.bind(this);
detail_params["top_el"] = top_el;
commonCls.send(detail_params);
return;
}
var parent_el = Element.getParentElement(el);
for (var i = 0; i < parent_el.childNodes.length; i++) {
var div = parent_el.childNodes[i];
if(div && div.tagName == "DIV" && Element.hasClassName(div,class_name)) {
if(Element.hasClassName(div,"display-none")) {
if(div.innerHTML != "") {
Element.removeClassName(div,"display-none");
}
var display_flag = true;
break;
} else {
Element.addClassName(div,"display-none");
var display_flag = false;
break;
}
}
}
var img_el= top_node_el.getElementsByTagName("img")[0];
if(img_el) {
if(display_flag) {
img_el.src = img_el.src.replace("right_arrow.gif","down_arrow.gif");
} else {
img_el.src = img_el.src.replace("down_arrow.gif","right_arrow.gif");
}
}
},
menuleafClick: function(this_el, url, page_id, edit_flag) {
var top_table_el = Element.getParentElementByClassName(this_el, "menu_row_top");
var top_class_name = "_menutop_" + page_id;
var top_el = $(this.id);
var top_node_el = Element.getChildElementByClassName(top_el,top_class_name);
var child_el = Element.getChildElement(top_table_el);
if(edit_flag) {
if(Element.hasClassName(child_el,"highlight")) {
var highlight_flag = true;
} else {
var highlight_flag = false;
this.cancelActiveBtn();
this.addActiveBtn(top_table_el);
}
}
var child_el = Element.getChildElement(top_node_el);
var input_el = Element.getChildElement(child_el);
if(!input_el && edit_flag) {
var input = document.createElement("INPUT");
input.type = "text";
input.value = child_el.innerHTML.unescapeHTML();;
child_el.innerHTML = "";
child_el.appendChild(input);
this.menuRenameFocus(input);
this._editObserver(input);
}
this.url = url;
},
addActiveBtn: function(el) {
var child_el = Element.getChildElement(el);
if(!Element.hasClassName(child_el,"highlight")) {
Element.addClassName(child_el,"highlight");
this._chgImage(child_el, true);
}
},
cancelActiveBtn: function() {
var active_el = this._searchActiveBtn();
if(!active_el) return;
var child_el = Element.getChildElement(active_el);
if(Element.hasClassName(child_el,"highlight")) {
Element.removeClassName(child_el,"highlight");
this._chgImage(child_el);
}
},
_searchActiveBtn: function() {
var top_el = $(this.id);
var tableList = top_el.getElementsByTagName("table");
for (var i = 0; i < tableList.length; i++){
if(Element.hasClassName(tableList[i],"highlight")) {
return tableList[i].parentNode;
}
}
},
_chgImage: function (el, active_flag) {
var img_el = Element.getChildElementByClassName(el, "_menu_displayseq");
if(img_el) {
if(active_flag) {
img_el.src = img_el.src.replace("move_bar.gif","move_bar_active.gif");
} else {
img_el.src = img_el.src.replace("move_bar_active.gif","move_bar.gif");
}
}
},
insPage: function(category_flag) {
var active_el = this._searchActiveBtn();
var node_type_el = Element.getChildElementByClassName(active_el, "_menu_node_type");
if(Element.hasClassName(node_type_el, "menu_node") || Element.hasClassName(node_type_el, "menu_room")) {
var node_flag = true;
} else {
var node_flag = false;
}
var href_el = Element.getChildElementByClassName(active_el, "_menutop");
var div_el = Element.getChildElement(href_el);
var idName = href_el.id;
var page_id = parseInt(idName.replace("_menutop"+this.id+"_",""));
var top_el = $(this.id);
if(node_flag) {
var visibility_el = $("_menuvisibility"+this.id+"_" + page_id);
if(!visibility_el) {
return;
} else if(visibility_el.src != undefined && visibility_el.src.match("off.gif")) {
var id_arr = active_el.id.split("_");
var room_id = id_arr[3];
var space_type = id_arr[4];
if(this.flat_flag == true && space_type == 1) {
if(!this.chkVisibility(active_el)) {
var visibility_flag = 0;
} else {
var visibility_flag = 1;
}
} else
var visibility_flag = 0;
} else {
var visibility_flag = 1;
}
} else {
if(!this.chkVisibility(active_el)) {
var visibility_flag = 0;
} else {
var visibility_flag = 1;
}
}
var ins_params = new Object();
ins_params["method"] = "post";
if(category_flag) {
ins_params["param"] = {"action":"menu_action_edit_addpage","main_page_id":page_id, "node_flag":"1", "visibility_flag":visibility_flag};
} else {
ins_params["param"] = {"action":"menu_action_edit_addpage","main_page_id":page_id, "visibility_flag":visibility_flag};
}
ins_params["top_el"] = top_el;
ins_params["callbackfunc"] = function(res){
if(res == "") {
return;
}
if(node_flag) {
var next_el = active_el.nextSibling;
var id_name = "_menu_"+page_id+this.id;
} else {
var next_el = active_el.parentNode;
var id_name = next_el.id;
}
if(next_el && next_el.id == id_name) {
var div = document.createElement("DIV");
next_el.appendChild(div);
div.innerHTML = res;
var new_el = Element.getChildElement(div);
var observe_el = Element.getChildElement(div);
next_el.appendChild(observe_el);
Element.remove(div);
if(next_el.innerHTML != "") {
Element.removeClassName(next_el,"display-none");
}
} else {
var div = document.createElement("DIV");
div.id = "_menu_"+page_id+this.id;
div.className = "_menu_"+page_id+this.id;
if(!next_el) {
active_el.parentNode.appendChild(div);
} else {
next_el.parentNode.insertBefore(div, next_el);
}
div.innerHTML = res;
var new_el = Element.getChildElement(div);
var observe_el = Element.getChildElement(div);
}
var new_href_el = Element.getChildElementByClassName(new_el, "_menutop");
new_href_el.onclick();
var input = new_href_el.getElementsByTagName("input")[0];
this.menuRenameFocus(input);
this._editObserver(input);
this._dndObserver(observe_el);
}.bind(this);
commonCls.send(ins_params);
},
delPage: function(page_id, id_name) {
var top_el = $(this.id);
var del_params = new Object();
del_params["method"] = "post";
del_params["param"] = {"action":"menu_action_edit_deletepage","main_page_id":page_id};
del_params["top_el"] = top_el;
del_params["callbackfunc"] = function(res){
if(res != "true") {
location.href = res;
} else {
var del_el = $(id_name);
var parent_el = del_el.parentNode.previousSibling;
this.cancelActiveBtn();
this.addActiveBtn(parent_el);
Element.remove(del_el);
var child_el = $("_menu_" + page_id+this.id);
if(child_el) {
var parent_el = child_el.parentNode;
Element.remove(child_el);
if(parent_el.innerHTML=="") {
Element.remove(parent_el);
}
}
}
}.bind(this);
commonCls.send(del_params);
},
chkVisibility: function(menu_top_el) {
var id_arr = menu_top_el.id.split("_");
var room_id = id_arr[3];
var space_type = id_arr[4];
if(menu_top_el && menu_top_el.parentNode) {
if(this.flat_flag == true && space_type == 1) {
parent_page_id = room_id;
} else {
var idName = menu_top_el.parentNode.id;
var parent_page_id = idName.replace("_menu_","");
var rObj = new RegExp(this.id+"$");
parent_page_id = parent_page_id.replace(rObj,"");
}
if(parent_page_id != "" && !(this.flat_flag == true && space_type == 1)) {
parent_page_id = parseInt(parent_page_id);
var parent_visibility_el = $("_menuvisibility"+this.id+"_" + parent_page_id);
if(parent_visibility_el) {
if(parent_visibility_el.src && parent_visibility_el.src.match("off.gif")) {
return false;
}
}
}
}
return true;
},
chgVisibilityPage: function(this_el, page_id) {
var menu_top_el = Element.getParentElementByClassName(this_el, "menu_row_top");
var id_arr = menu_top_el.id.split("_");
var room_id = id_arr[3];
var space_type = id_arr[4];
if(!this.chkVisibility(menu_top_el)) return;
var img_el = Element.getChildElement(this_el);
if(img_el.src.match("on.gif")) {
var visibility_flag = 0;
} else {
var visibility_flag = 1;
}
var top_el = $(this.id);
var chg_params = new Object();
chg_params["method"] = "post";
chg_params["param"] = {"action":"menu_action_edit_visibility","main_page_id":page_id, "visibility_flag":visibility_flag};
chg_params["top_el"] = top_el;
chg_params["callbackfunc"] = function(res){
if(visibility_flag) {
img_el.src = img_el.src.replace("off.gif","on.gif");
} else {
img_el.src = img_el.src.replace("on.gif","off.gif");
}
var next_el = menu_top_el.nextSibling;
if(this.flat_flag == false || (this.flat_flag == true && space_type != 1)) {
if(next_el && next_el.id == "_menu_" + page_id+this.id) {
var visibilityfields = Element.getElementsByClassName(next_el, "_menuvisibility");
visibilityfields.each(function(visibility_el) {
if(visibility_flag) {
visibility_el.src = visibility_el.src.replace("off.gif","on.gif");
} else {
visibility_el.src = visibility_el.src.replace("on.gif","off.gif");
}
}.bind(this));
}
}
}.bind(this);
commonCls.send(chg_params);
},
menuJqLoad: function(block_id, color) {
var jqload_func = this._menuJqLoad;
var cancel_func = this._menuCancelForJqload;
if($('login_id_0') && browser.isIE && browser.version < 9) {
var els = Element.getElementsByClassName(this.id, "menu_jq_gnavi_pldwn_" + color + "_btn");
els.each(
function(el){
el.observe("click", cancel_func);
}
);
setTimeout(function(){
jqload_func(block_id, color);
}, 400);
}
else {
jqload_func(block_id, color);
}
},
_menuCancelForJqload : function(e) {
jcheck = new Function('return !(typeof jQuery !== "undefined" && window.$ === jQuery)');
commonCls.wait(jcheck);
return false;
},
_menuJqLoad : function(block_id, color) {
var cancel_func = this._menuCancelForJqload;
jqcheckCls.jqload("jquery-1.6.4.min", "window.jQuery",
function() {
var zindex = 900;
if(browser.isIE && browser.version <= 7) {
jQuery("#" + block_id + " ul.menu_jq_gnavi_pldwn_" + color + " > li").each(function(index,el) {
jQuery(el).attr("style","z-index:"+zindex);
zindex--;
});
}
if(browser.isIE && browser.version < 7) {
jQuery("#" + block_id + " ul.menu_jq_gnavi_pldwn_" + color + " li").hover(
function() { jQuery(">a", this).addClass("menu_jq_gnavi_pldwn_" + color + "_actives"); jQuery(">ul.menu_jq_gnavi_pldwn_" + color + "_sub:not(:animated)", this).show(); },
function() { jQuery(">a", this).removeClass("menu_jq_gnavi_pldwn_" + color + "_actives"); jQuery(">ul.menu_jq_gnavi_pldwn_" + color + "_sub", this).hide(); }
);
}
else {
jQuery("#" + block_id + " ul.menu_jq_gnavi_pldwn_" + color + " li").hover(
function() {
jQuery(">a", this).addClass("menu_jq_gnavi_pldwn_" + color + "_actives");
if(jQuery(this).closest("ul").hasClass("menu_jq_gnavi_pldwn_" + color + "_sub")) {
if(jQuery(">ul.menu_jq_gnavi_pldwn_" + color + "_sub", this).css("left")=="0px" || jQuery(">ul.menu_jq_gnavi_pldwn_" + color + "_sub", this).css("left") == "auto"){
jQuery(">ul.menu_jq_gnavi_pldwn_" + color + "_sub", this).css("left", jQuery(this).width());
}
}
jQuery(">ul.menu_jq_gnavi_pldwn_" + color + "_sub:not(:animated)", this).slideDown("fast");
},
function() {
jQuery(">a", this).removeClass("menu_jq_gnavi_pldwn_" + color + "_actives");
jQuery(">ul.menu_jq_gnavi_pldwn_" + color + "_sub", this).slideUp("fast");
}
);
}
if($('login_id_0') && browser.isIE && browser.version < 9) {
Element.getElementsByClassName(this.id, "menu_jq_gnavi_pldwn_" + color + "_btn").each(
function(el){
el.stopObserving("click", cancel_func);
}
);
}
}
);
}
}
var clsAnnouncement = Class.create();
var announcementCls = Array();
clsAnnouncement.prototype = {
initialize: function(id) {
this.id = id;
this.textarea = null;
this.textarea_more = null;
},
announcementMainShow: function() {
commonCls.sendView(this.id,"announcement_view_main_init");
},
announcementEditShow: function() {
commonCls.sendView(this.id,"announcement_view_edit_init");
},
announcementEditInit: function() {
this.textarea = new compTextarea();
this.textarea.uploadAction = {
image    : "announcement_action_upload_image",
file     : "announcement_action_upload_init"
};
this.textarea.focus = true;
this.textarea.textareaShow(this.id, "comptextarea", "full");
},
moreInit: function() {
if(this.textarea_more == null) {
this.textarea_more = new compTextarea();
this.textarea_more.uploadAction = {
image    : "announcement_action_upload_image",
file     : "announcement_action_upload_init"
};
}
this.textarea_more.textareaShow(this.id, "textarea_more"+this.id, "full");
},
announcementRegist: function(form_el) {
var top_el = $(this.id);
var more_checked = 0;
if(form_el.more_checkbox.checked) {
more_checked = 1;
}
var more_title = null;
var more_content = null;
var hide_more_title = null;
if(this.textarea_more != null) {
more_title = form_el.more_title.value;
more_content = this.textarea_more.getTextArea();
hide_more_title = form_el.hide_more_title.value;
}
var content = this.textarea.getTextArea();
var ins_params = new Object();
ins_params["method"] = "post";
ins_params["param"] = {"action":"announcement_action_edit_init",
"content":content,
"more_checked":more_checked,
"more_content":more_content,
"more_title":more_title,
"hide_more_title":hide_more_title
};
ins_params["top_el"] = top_el;
ins_params["loading_el"] = top_el;
ins_params["callbackfunc"] = function(){this.announcementMainShow();}.bind(this);
commonCls.send(ins_params);
},
checkMore: function(check_el, confirmMessage) {
if(check_el.checked == true) {
commonCls.displayChange($('announcement_more_content' + this.id));
this.moreInit();
}else {
if (!commonCls.confirm(confirmMessage)) {
check_el.checked = true;
return false;
}
commonCls.displayChange($('announcement_more_content' + this.id));
}
},
announcementCancel: function() {
this.announcementMainShow();
}
}
var clsWhatsnew = Class.create();
var whatsnewCls = Array();
clsWhatsnew.prototype = {
initialize: function(id, block_id) {
this.id = id;
this.block_id = block_id;
this.params = new Object();
this.rss_action = "whatsnew_view_main_rss";
this.strlen = 80;
this.more_str = "";
this.WHATSNEW_DEF_FLAT = "0";
this.WHATSNEW_DEF_MODULE = "1";
this.WHATSNEW_DEF_ROOM = "2";
},
showOriginal: function(whatsnew_id, parameter) {
var params = new Object();
params["callbackfunc"] = function(res) {
location.href = _nc_base_url + _nc_index_file_name + "?" + parameter;
}.bind(this);
commonCls.sendPost(this.id, 'action=whatsnew_action_main_read&whatsnew_id=' + whatsnew_id, params);
},
switchMain: function(display_type, display_value, flag) {
var top_el = $(this.id);
var href_param_str = "&block_id=" + this.block_id;
if (!display_type) {
var display_type_el = $("whatsnew_display_type" + this.id);
if (display_type_el) {
var display_type = display_type_el.value;
}
}
if (display_type) {
href_param_str += "&display_type=" + display_type;
}
if (!display_value && flag == 0) {
var display_days_el = $("whatsnew_display_days" + this.id);
if (display_days_el) {
var display_days = display_days_el.value;
}
}else if(!display_value && flag == 1){
var the_number_of_display_el = $("whatsnew_the_number_of_display" + this.id);
if (the_number_of_display_el) {
var display_number = the_number_of_display_el.value;
}
}
if (display_value && flag == 0) {
href_param_str += "&display_days=" + display_value;
}else if(display_value && flag == 1){
href_param_str += "&display_number=" + display_value;
}
href_param_str += "&_header=0";
var rss_el = $("whatsnew_rss" + this.id);
if (rss_el) {
rss_el.href = _nc_base_url + _nc_index_file_name + "?action=" + this.rss_action + href_param_str;
}
var params = new Object();
params["loading_el"] = top_el;
params["top_el"] = top_el;
params["method"] = "post";
params["param"] = new Object();
params["param"]["action"] = "whatsnew_action_main_result";
if (display_type) {
params["param"]["display_type"] = display_type;
}
if (display_value && flag == 0) {
params["param"]["display_days"] = display_value;
}else if(display_value && flag == 1){
params["param"]["display_number"] = display_value;
}
params["target_el"] = $("whatsnew_contents" + this.id);
commonCls.send(params);
},
switchDisplayType: function(form_el, not_checked) {
if (form_el.display_type.value == this.WHATSNEW_DEF_ROOM) {
form_el.display_room_name.checked = false;
form_el.display_room_name.disabled = true;
var el = $("whatsnew_display_room_name" + this.id);
if (!Element.hasClassName(el.nextSibling, "disable_lbl")) {
Element.addClassName(el.nextSibling, "disable_lbl");
}
if (!not_checked) {
form_el.display_module_name.checked = true;
}
form_el.display_module_name.disabled = false;
var el = $("whatsnew_display_module_name" + this.id);
if (Element.hasClassName(el.nextSibling, "disable_lbl")) {
Element.removeClassName(el.nextSibling, "disable_lbl");
}
} else if (form_el.display_type.value == this.WHATSNEW_DEF_MODULE) {
form_el.display_module_name.checked = false;
form_el.display_module_name.disabled = true;
var el = $("whatsnew_display_module_name" + this.id);
if (!Element.hasClassName(el.nextSibling, "disable_lbl")) {
Element.addClassName(el.nextSibling, "disable_lbl");
}
if (!not_checked) {
form_el.display_room_name.checked = true;
}
form_el.display_room_name.disabled = false;
var el = $("whatsnew_display_room_name" + this.id);
if (Element.hasClassName(el.nextSibling, "disable_lbl")) {
Element.removeClassName(el.nextSibling, "disable_lbl");
}
} else {
if (!not_checked) {
form_el.display_room_name.checked = true;
}
form_el.display_room_name.disabled = false;
var el = $("whatsnew_display_room_name" + this.id);
if (Element.hasClassName(el.nextSibling, "disable_lbl")) {
Element.removeClassName(el.nextSibling, "disable_lbl");
}
if (!not_checked) {
form_el.display_module_name.checked = true;
}
form_el.display_module_name.disabled = false;
var el = $("whatsnew_display_module_name" + this.id);
if (Element.hasClassName(el.nextSibling, "disable_lbl")) {
Element.removeClassName(el.nextSibling, "disable_lbl");
}
}
},
changeStyle: function(form_el) {
commonCls.sendPost(this.id, 'action=whatsnew_action_edit_style&' + Form.serialize(form_el), {"target_el":$(this.id)});
},
setSelectRoom: function(form_el) {
var params = new Object();
params["callbackfunc"] = function(res) {
commonCls.removeBlock(this.id);
}.bind(this);
commonCls.frmAllSelectList(form_el, "not_enroll_room[]");
commonCls.frmAllSelectList(form_el, "enroll_room[]");
commonCls.sendPost(this.id, Form.serialize(form_el), params);
},
changeDisplayType: function(flg) {
if(flg == 1){
$("whatsnew_the_number_of_display" + this.id).disabled = true;
$("whatsnew_display_days" + this.id).disabled = false;
}else{
$("whatsnew_the_number_of_display" + this.id).disabled = false;
$("whatsnew_display_days" + this.id).disabled = true;
}
}
}
var clsCounter = Class.create();
var counterCls = Array();
clsCounter.prototype = {
initialize: function(id) {
this.id = id;
},
counterMainShow: function() {
commonCls.sendView(this.id,"counter_view_main_init");
},
counterPreview: function() {
var top_el = $(this.id);
var form = top_el.getElementsByTagName("form")[0];
var preview_el = $("_counter_preview"+this.id);
var params = new Object();
params["method"] = "get";
params["param"] = "action=counter_view_edit_preview" + "&"+ Form.serialize(form);
params["top_el"] = top_el;
params["loading_el"] = preview_el;
params["target_el"] = preview_el;
commonCls.send(params);
},
counterRegist: function(cmd) {
var top_el = $(this.id);
var form = top_el.getElementsByTagName("form")[0];
var params = new Object();
params["method"] = "post";
params["param"] = "action=counter_action_edit_init&zero_flag="+ cmd + "&"+ Form.serialize(form);
params["top_el"] = top_el;
params["loading_el"] = top_el;
params["target_el"] = top_el;
commonCls.send(params);
}
}
var clsLinklist = Class.create();
var linklistCls = Array();
clsLinklist.prototype = {
initialize: function(id) {
this.id = id;
this.currentLinklistID = null;
this.linklist_id = null;
this.target = null;
this.viewCountFlag = false;
this.popup = null;
this.popupForm = null;
this.entry = null;
this.oldURL = null;
this.automatic = false;
this.automaticError = false;
this.searchResults = new Array();
},
checkCurrent: function() {
var currentRow = $("linklist_current_row" + this.currentLinklistID + this.id);
if (!currentRow) {
return;
}
Element.addClassName(currentRow, "highlight");
var current = $("linklist_current" + this.currentLinklistID + this.id);
current.checked = true;
},
changeCurrent: function(linklistID) {
var oldCurrentRow = $("linklist_current_row" + this.currentLinklistID + this.id);
if (oldCurrentRow) {
Element.removeClassName(oldCurrentRow, "highlight");
}
this.currentLinklistID = linklistID;
var currentRow = $("linklist_current_row" + this.currentLinklistID + this.id);
Element.addClassName(currentRow, "highlight");
var post = {
"action":"linklist_action_edit_current",
"linklist_id":linklistID
};
var params = new Object();
params["callbackfunc_error"] = function(res) {
commonCls.alert(res);
commonCls.sendView(this.id, "linklist_view_edit_list");
}.bind(this);
commonCls.sendPost(this.id, post, params);
},
referenceLinklist: function(event, linklistID, prefixID) {
var params = new Object();
params["action"] = "linklist_view_main_init";
params["linklist_id"] = linklistID;
params["prefix_id_name"] = prefixID;
var popupParams = new Object();
var top_el = $(this.id);
popupParams['top_el'] = top_el;
popupParams['target_el'] = top_el;
popupParams['center_flag'] = true;
commonCls.sendPopupView(event, params, popupParams);
},
deleteLinklist: function(linklistID, confirmMessage) {
if (!commonCls.confirm(confirmMessage)) return false;
var post = {
"action":"linklist_action_edit_delete",
"linklist_id":linklistID
};
var params = new Object();
params["target_el"] = $(this.id);
params["callbackfunc_error"] = function(res) {
commonCls.sendView(this.id, "linklist_view_edit_list");
}.bind(this);
commonCls.sendPost(this.id, post, params);
},
selectDisplayDropdown: function() {
var element = $("linklist_display_description" + this.id);
element.checked = false;
element.disabled = true;
Element.addClassName($("linklist_display_description_label" + this.id), "disable_lbl");
},
selectDisplayList: function() {
$("linklist_display_description" + this.id).disabled = false;
Element.removeClassName($("linklist_display_description_label" + this.id), "disable_lbl");
},
selectItem: function(select, hiddenElement, value) {
var items = select.parentNode.childNodes;
for (var i = 0,length = items.length; i < length; i++) {
Element.removeClassName(items[i], "highlight");
}
Element.addClassName(select, "highlight");
hiddenElement.value = value;
},
showPopup: function(action, eventElement) {
this.popup = new compPopup(this.id,this.id);
this.popup.modal = true;
this.popup.loadObserver = function() {
this.popupForm = this.popup.popupElement.contentWindow.document.getElementsByTagName("form")[0];
if (this.popupForm["url"]) {
commonCls.focus(this.popupForm["url"]);
} else {
commonCls.focus(this.popupForm);
}
}.bind(this);
var params = new Object();
params["param"] = {
"action":action
};
params["top_el"] = this.id;
params["callbackfunc"] = function(res) {
this.popup.showPopup(res, eventElement);
}.bind(this);
commonCls.send(params);
},
changeAutomatic: function(checked) {
commonCls.displayChange(this.popupForm["automatic_title"]);
commonCls.displayChange(this.popupForm["title"]);
commonCls.displayChange(this.popupForm["automatic_description"]);
commonCls.displayChange(this.popupForm["description"]);
if (!checked) {
this.popupForm["title"].focus();
this.popupForm["title"].select();
} else {
this.getLink();
}
},
getLink: function() {
var form = this.popupForm;
if (form["url"].value == this.oldURL
|| !form["automatic_check"].checked) {
return;
}
if (this.automatic) {
return;
}
this.automatic = true;
var params = new Object();
params["param"] = {
"action":"linklist_view_main_automatic",
"url":form["url"].value,
"page_id":_nc_main_page_id
};
params["top_el"] = form;
params["callbackfunc"] = function(res) {
var tag = res.getElementsByTagName("title")[0];
if (tag.firstChild) {
form["automatic_title"].value = tag.firstChild.nodeValue;
}
var tag = res.getElementsByTagName("description")[0];
if (tag.firstChild) {
form["automatic_description"].value = tag.firstChild.nodeValue;
} else {
form["automatic_description"].value = "";
}
if (!Element.hasClassName(form["automatic_title"], "display-none")) {
form["title"].value = form["automatic_title"].value;
}
if (!Element.hasClassName(form["automatic_description"], "display-none")) {
form["description"].value = form["automatic_description"].value;
}
this.oldURL = form["url"].value;
this.automatic = false;
this.automaticError = false;
}.bind(this);
params["callbackfunc_error"] = function(res) {
commonCls.alert(res);
this.automatic = false;
this.automaticError = true;
}.bind(this);
commonCls.send(params);
},
changeDescription: function() {
commonCls.displayChange(this.popupForm.firstChild.rows[1]);
this.popup.resize();
},
link: function(linkID, url, viewCountElement) {
var post = {
"action":"linklist_action_main_count",
"linklist_id":this.linklist_id,
"link_id":linkID
};
var params = new Object();
if (this.viewCountFlag) {
params["callbackfunc"] = function(res) {
if (viewCountElement.tagName == "OPTION") {
var tag = res.getElementsByTagName("option")[0];
} else {
var tag = res.getElementsByTagName("list")[0];
}
if (tag.firstChild) {
viewCountElement.innerHTML = tag.firstChild.nodeValue;
}
}.bind(this);
}
commonCls.sendPost(this.id, post, params);
if (this.target) {
window.open(url, this.target);
} else {
location.href = url;
}
},
selectLink: function(select, target) {
if (select.value.length == 0)  {
return;
}
var values = select.value.split("|");
var targetElement = select.options[select.selectedIndex];
this.link(values[0], values[1], targetElement);
},
showInputElement: function(element) {
commonCls.displayNone(element);
commonCls.displayVisible(element.nextSibling);
commonCls.focus(element.nextSibling);
},
hideInputElement: function(element) {
commonCls.displayNone(element);
commonCls.displayVisible(element.previousSibling);
},
enterCategory: function(category, category_id) {
if (category == null) {
category = this.popupForm["category_name"];
}
var post = {
"action":"linklist_action_main_category_entry",
"category_id":category_id,
"category_name":category.value,
"entry":this.entry
};
var params = new Object();
if (category_id == null) {
params["target_el"] = $(this.id);
params["callbackfunc"] = function(res) {
this.popup.closePopup();
}.bind(this);
} else {
params["callbackfunc"] = function(res) {
category.previousSibling.innerHTML = category.value.escapeHTML();
this.hideInputElement(category);
}.bind(this);
}
commonCls.sendPost(this.id, post, params);
},
deleleCategory: function(category_id, confirmMessage) {
if (!commonCls.confirm(confirmMessage)) return false;
var post = {
"action":"linklist_action_main_category_delete",
"category_id":category_id
};
var params = new Object();
params["callbackfunc"] = function(res) {
var categoryElement = $("linklist_category_link" + category_id + this.id);
Element.remove(categoryElement);
}.bind(this);
commonCls.sendPost(this.id, post, params);
},
enterLink: function(element, link_id) {
if (link_id == null) {
this.getLink();
if (this.automaticError) {
this.automaticError = false;
return;
}
if (this.automatic) {
setTimeout(function() {this.enterLink(element, link_id);}.bind(this), 300);
return;
}
}
var params = new Object();
if (link_id == null) {
if (this.popupForm["automatic_check"].checked) {
if (this.popupForm["automatic_title"].value != "") {
var title = this.popupForm["automatic_title"].value;
} else {
var title = "";
}
if (this.popupForm["automatic_description"].value != "") {
var description = this.popupForm["automatic_description"].value;
} else {
var description = "";
}
} else {
var title = this.popupForm["title"].value;
var description = this.popupForm["description"].value;
}
var post = {
"action":"linklist_action_main_link_entry",
"category_id":this.popupForm["category_id"].value,
"title":title,
"url":this.popupForm["url"].value,
"description":description,
"entry":this.entry
};
if (this.popupForm["automatic_check"].checked) {
post["automatic_check"] = this.popupForm["automatic_check"].value;
}
params["target_el"] = $(this.id);
params["callbackfunc"] = function(res) {
this.popup.closePopup();
}.bind(this);
} else {
var post = {
"action":"linklist_action_main_link_entry",
"link_id":link_id,
"entry":this.entry
};
post[element.name] = element.value;
params["callbackfunc"] = function(res) {
var value = element.value;
if (element.tagName == "TEXTAREA") {
element = element.parentNode;
}
element.previousSibling.innerHTML = value.escapeHTML();
this.hideInputElement(element);
}.bind(this);
}
commonCls.sendPost(this.id, post, params);
},
deleleLink: function(link_id, confirmMessage) {
if (!commonCls.confirm(confirmMessage)) return false;
var post = {
"action":"linklist_action_main_link_delete",
"link_id":link_id
};
var params = new Object();
params["callbackfunc"] = function(res) {
var linkElement = $("linklist_link" + link_id + this.id);
Element.remove(linkElement);
}.bind(this);
commonCls.sendPost(this.id, post, params);
},
changeSequence: function(drag_id, drop_id, position) {
if (drag_id.match(/linklist_category_link/)) {
var post = {
"action":"linklist_action_main_category_sequence",
"drag_category_id":drag_id.match(/\d+/)[0],
"drop_category_id":drop_id.match(/\d+/)[0],
"position":position
};
} else if (drop_id.match(/linklist_category/)) {
var post = {
"action":"linklist_action_main_link_sequence",
"drag_link_id":drag_id.match(/\d+/)[0],
"drop_category_id":drop_id.match(/\d+/)[0]
};
} else {
var post = {
"action":"linklist_action_main_link_sequence",
"drag_link_id":drag_id.match(/\d+/)[0],
"drop_link_id":drop_id.match(/\d+/)[0],
"position":position
};
}
commonCls.sendPost(this.id, post);
},
search: function() {
var params = new Object();
params["param"] = {
"action":"linklist_view_main_search_result",
"search":this.popupForm["search"].value
};
params["top_el"] = this.id;
params["callbackfunc"] = function(res) {
if (this.searchResults.length > 0) {
var beforeElement = this.searchResults[this.searchResults.length - 1];
commonCls.displayNone(beforeElement);
}
var resultElement = this.popup.popupElement.contentWindow.document.createElement("DIV");
resultElement.innerHTML = res;
var re_words = new RegExp(this.popupForm["search"].value, 'i');
var hits = 0;
var replacer = function(str) {
hits++;
return '<span class="linklist_highlight">' + str + '</span>'
};
var titles = resultElement.getElementsByTagName("a");
$A(titles).each(
function(title) {
title.innerHTML = title.innerHTML.gsub(re_words, replacer);
}
);
var descriptions = resultElement.getElementsByTagName("div");
$A(descriptions).each(
function(description) {
if (description.className != 'linklist_description') return;
description.innerHTML = description.innerHTML.gsub(re_words, replacer);
}
);
if (hits > 0) {
} else {
resultElement.innerHTML += '0 hits';
}
this.popupForm.appendChild(resultElement);
this.searchResults.push(resultElement);
this.popup.resize();
}.bind(this);
commonCls.send(params);
}
}
var clsLogin = Class.create();
var loginCls = Array();
clsLogin.prototype = {
initialize: function(id) {
this.id = id;
this.sslIframeSrc = null;
this.loginIdValue = null;
},
showLogin: function(event) {
commonCls.displayVisible($('login_popup'));
commonCls.sendPopupView(event, {'action':'login_view_main_init'}, {'center_flag':true});
var sslIframe = $('login_ssl_iframe' + this.id);
if (sslIframe == null) {
this.initializeFocus();
return;
}
if (this.sslIframeSrc != null) {
sslIframe.focus();
sslIframe.src = this.sslIframeSrc;
}
},
initializeFocus: function(errorCount) {
try {
var formElement = $('login_form' + this.id);
var loginIdElement = formElement['login_id'];
loginIdElement.focus();
loginIdElement.select();
if (browser.isIE) {
loginIdElement.fireEvent('ondblclick');
loginIdElement.fireEvent('onblur');
}
}catch(e){
if (errorCount < 5) {
errorCount++;
setTimeout(function(){this.initializeFocus(errorCount);}.bind(this), 300);
}
}
},
setButtonStyle: function(element) {
if (element == null) {
return;
}
var styleValue = "border-radius:4px;-webkit-border-radius:4px;-moz-border-radius:4px;";
element.setAttribute('style', styleValue);
},
loginLogout: function(event) {
var load_el = Event.element(event);
var logout_params = new Object();
var top_el = $(this.id);
logout_params["method"] = "post";
logout_params["param"] = {"action":"login_action_main_logout"};
logout_params["loading_el"] = load_el;
commonCls.send(logout_params);
},
insAutoregist: function (form_el) {
var reg_params = new Object();
reg_params["param"] = {'action': "login_action_main_autoregist"};
reg_params["form_el"] = form_el;
reg_params["top_el"] = $(this.id);
reg_params["target_el"] = $("target"+this.id);
reg_params["method"] = "post";
reg_params['form_prefix'] = "login_attachment";
reg_params["callbackfunc_error"] = function(file_list, res){
this.focusError(res);
}.bind(this);
commonCls.sendAttachment(reg_params);
},
focusError: function(res) {
res = commonCls.cutErrorMes(res);
if(res.match(":")) {
var mesArr = res.split(":");
var alert_res = "";
for(var i = 1; i < mesArr.length; i++) {
alert_res += mesArr[i];
}
var focus_el = $("login_items"+ this.id + "_" + mesArr[0]);
if(focus_el) {
commonCls.alert(alert_res);
commonCls.focus(focus_el);
} else {
commonCls.alert(alert_res);
}
} else {
commonCls.alert(res);
}
}
}
var clsPm = Class.create();
var pmCls = Array();
clsPm.prototype = {
initialize: function(id, parent_id) {
this.top_el_id = null;
this.id = id;
this.filterFlag = null;
this.ascImg = null;
this.descImg = null;
this.sortCol = null;
this.sortDir = "DESC";
this.oldSortCol = null;
this.textarea = null;
this.mailbox = 0;
this.page = 1;
this.ccIndex = 1;
this.search_date_from = null;
this.search_date_to = null;
this.popup_editor_count = 0;
this.backup_avatar_html = "";
this.parent_id = (parent_id != "") ? parent_id : null;
this.selectUserCallback = null;
},
sortBy: function(sort_col, search_flag) {
if(this.sortCol == null) {
this.sortDir = "DESC";
}else {
if(this.sortCol != sort_col) {
this.oldSortCol = this.sortCol;
this.sortDir = "DESC";
}else {
if(this.sortDir == "DESC") {
this.sortDir = "ASC";
}else {
this.sortDir = "DESC";
}
}
}
this.sortCol = sort_col;
this.sortMethod(search_flag);
},
sortMethod: function(search_flag) {
var top_el = $(this.id);
var params = new Object();
var action = "pm_view_main_init";
if(search_flag == 'search'){
action = "pm_view_main_search_result";
this.mailbox = 4;
}
params["param"] = {
"action":action,
"sort_col":this.sortCol,
"sort_dir":this.sortDir,
"filter":this.filterFlag,
"mailbox":this.mailbox,
"search_flag":search_flag
};
params["top_el"] = top_el;
params["loading_el"] = top_el;
params["target_el"] = top_el;
params["callbackfunc"] = function(res) {
var imgObj = $("pm_sort_img" + this.id + "_" + this.sortCol);
if (this.sortDir == "ASC") {
imgObj.src = this.ascImg;
} else {
imgObj.src = this.descImg;
}
commonCls.displayVisible(imgObj);
if (this.oldSortCol != null) {
var oldImgObj = $("pm_sort_img" + this.id + "_" + this.oldSortCol);
commonCls.displayNone(oldImgObj);
this.oldSortCol = null;
}
}.bind(this);
commonCls.send(params);
},
showMessagePopup: function(receiver_id, sender_handle, eventElement,flag, parent_el_id, top_id_name) {
sender_handle = sender_handle.unescapeHTML();
if(receiver_id > 0){
var prefix_id_name = "popup_message_reply_" + receiver_id;
}else{
var prefix_id_name = "popup_message_new_" + this.popup_editor_count;
this.popup_editor_count++;
}
if(parent_el_id != null){
this.id = top_id_name;
}else{
var top_id_name = this.id;
}
var params = new Object();
params["top_el"] = $(this.id);
params["modal_flag"] = false;
params["center_flag"] = true;
params["loading_el"] = $(this.id);
params["callbackfunc"] = function(res){
if(parent_el_id != null && parent_el_id != "_active_center_0"){
commonCls.removeBlock(parent_el_id);
}
}.bind(this);
commonCls.sendPopupView(eventElement, {'action':'pm_view_main_message_entry','prefix_id_name': prefix_id_name,'receiver_id':receiver_id,'sender_handle':sender_handle,'flag':flag, 'top_el_id':this.id.replace("_", ""),'top_id_name':top_id_name}, params);
},
setMessage: function(form_el,sendFlag, top_el_id) {
if (sendFlag == 1) {
this.mailbox = "1";
} else if (sendFlag == 2) {
this.mailbox = "2";
}
var send_all_flag_value = 0;
if(form_el.send_all_flag != null){
send_all_flag_value = form_el.send_all_flag.value;
}
var top_el = $(this.id);
var params = new Object();
var messageBody = this.textarea.getTextArea();
params["param"] = "pm_action_main_message_entry&" + Form.serialize(form_el) +
"&body=" + encodeURIComponent(messageBody) +
"&sendFlag=" + sendFlag;
params["method"] = "post";
params["loading_el"] = top_el;
params["top_el"] = top_el;
if(!send_all_flag_value){
var receivers = document.getElementsByName("receivers[]");
var receiver_list = "";
for (var i=0; i<receivers.length; i++) {
receiver_list = receiver_list + receivers[i].value;
}
}else{
var receiver_list = "";
}
var subject = "";
if(form_el.subject != null){
subject = form_el.subject.value;
}
messageBody = messageBody.replace('<br />','');
messageBody = messageBody.replace( /\r|\n/g,'');
params["callbackfunc"] = function(res){
this.sendMail();
commonCls.removeBlock(this.id);
var parameters = new Object();
parameters["action"] = "pm_view_main_init";
parameters["mailbox"] = this.mailbox;
commonCls.sendView(top_el_id, parameters);
}.bind(this);
commonCls.send(params);
},
showDetailPopup: function(receiver_id, row_id, read_state, page, eventElement,search_flag) {
if(read_state <= 0){
this.listRowOnRead(this.id, row_id);
}
commonCls.sendPopupView(eventElement, {'prefix_id_name' : "popup_pm_message_detail" + receiver_id, 'action': "pm_view_main_message_detail", 'receiver_id': receiver_id, 'mailbox': this.mailbox, 'filter': this.filterFlag, 'page': page, 'parent_id_name': this.id, 'theme_name': 'system', 'top_el_id': this.id.replace("_", ""), 'search_flag': search_flag, 'top_id_name':this.id}, {'top_el':$(this.id), 'modal_flag':false, 'center_flag':true});
},
closeDetailsPopup: function(id, receiver_id, page_id){
if(this.id == "" || this.id == null) {
this.id = id;
}
var top_el = $(this.id.replace("_popup_pm_message_detail" + receiver_id, ""));
var params = new Object();
params["param"] = {
"action":"pm_view_main_init",
"mailbox":0,
"page_id":page_id
};
params["top_el"] = top_el;
params["loading_el"] = top_el;
params["target_el"] = top_el;
params["callbackfunc"] = function(res) {
var id_prefix_match = (this.id).match(new RegExp("_popup_pm_message_detail" + receiver_id));
if(id_prefix_match){
commonCls.removeBlock(this.id);
}
}.bind(this);
commonCls.send(params);
},
deleteTag: function(tag_id, confirmMessage) {
if (!confirm(confirmMessage)) return false;
var params = new Object();
var post = {
"action":"pm_action_main_tag_delete",
"tag_id":tag_id
};
params["target_el"] = $(this.id);
params["callbackfunc"] = function(res){
}.bind(this);
commonCls.sendPost(this.id, post, params);
},
pmEditInit: function() {
this.textarea = new compTextarea();
this.textarea.uploadAction = {
image    : "pm_action_upload_image",
file     : "pm_action_upload_file"
};
this.textarea.downloadAction = "pm_download_main";
this.textarea.focus = false;
this.textarea.textareaShow(this.id, "comptextarea", "simple");
},
pmEditSubjectInit: function(form_el, subject, reply_flag){
if(reply_flag == '1'){
var re_count = 0;
var re_match = subject.match(new RegExp("^(Re:){1,}"));
if (re_match) {
re_count = re_match[0].length / 3;
subject = subject.gsub(/Re:/, "");
}
var re_match = subject.match(new RegExp("^(Re([0-9]+):)"));
if (re_match) {
re_count += valueParseInt(re_match[2]);
subject = subject.replace(new RegExp(re_match[0]), "");
}
if (re_count > 0) {
form_el.subject.value = "Re" + (re_count + 1) + ":" + subject;
} else {
form_el.subject.value = "Re:" + subject;
}
}else{
form_el.subject.value = subject;
}
},
filter: function(form_el,filter) {
var top_el = $(this.id);
var params = new Object();
this.filterFlag = filter;
var search_flag = "none";
if(form_el.search_flag != null){ search_flag = form_el.search_flag.value; }
var main_window_action = "pm_view_main_init";
if(search_flag == 'search'){
main_window_action = "pm_view_main_search_result";
this.mailbox = 4;
}
params["param"] = {
"action":main_window_action,
"sort_col":this.sortCol,
"sort_dir":this.sortDir,
"filter":this.filterFlag,
"mailbox":this.mailbox,
"search_flag":search_flag
};
params["top_el"] = top_el;
params["loading_el"] = top_el;
params["target_el"] = top_el;
params["callbackfunc"] = function(res) {
this.setSortState();
}.bind(this);
commonCls.send(params);
},
showTagPopup: function(tag_id, detail_id, eventElement, receiver_list, search_flag, select_all_flag, top_id_name,parent_id_name) {
if(detail_id > 0){
this.id = top_id_name;
var top_el_id = top_id_name.replace('_', '');
} else {
var top_el_id = this.id.replace('_', '');
var top_id_name = this.id;
var parent_id_name = this.id;
}
commonCls.sendPopupView(eventElement, {'prefix_id_name':'popup_pm_tag_entry', 'action':'pm_view_main_tag_entry', 'top_el_id':top_el_id, 'tag_id':tag_id, 'receiver_list':receiver_list, 'mailbox':this.mailbox, 'search_flag':search_flag, 'select_all_flag':select_all_flag,'filter':this.filterFlag, 'sortCol':this.sortCol, 'sortDir':this.sortDir, 'page':this.page, 'top_id_name':top_id_name,'parent_id_name':parent_id_name}, {'top_el':$(this.id), 'modal_flag':true, 'center_flag':true});
if($("otherOp" + this.id)){
$("otherOp" + this.id).getElementsByTagName("option")[0].selected = true;
}
},
resetOperationBox: function(top_el_id){
if($("otherOp" + this.id)){
$("otherOp_" + top_el_id).getElementsByTagName("option")[0].selected = true;
}
},
enterTag: function(form_el, flag, receiver_list, mailbox, search_flag, top_el_id, parent_el_id) {
var params = new Object();
var post = Form.serialize(form_el);
var action = "";
if (flag == 1 &&  receiver_list != "") {
action ="pm_view_main_init";
if(search_flag == 'search'){
action = "pm_view_main_search_result";
this.mailbox = 4;
}
} else if (flag == 2 || receiver_list == "") {
action = "pm_view_main_tag_init"
}
var page = null;
if(form_el.page != null){
page = form_el.page.value;
}
var sortCol = null;
if(form_el.sortCol != null){
sortCol = form_el.sortCol.value;
}
var sortDir = null;
if(form_el.sortDir != null){
sortDir = form_el.sortDir.value;
}
var filter = null;
if(form_el.filter != null){
filter = form_el.filter.value;
}
params["callbackfunc"] = function(res){
commonCls.removeBlock(this.id);
var parameters = new Object();
parameters["action"] = action;
parameters["receiver_list"] = receiver_list;
parameters["mailbox"] = mailbox;
parameters["sort_col"] = sortCol;
parameters["sort_dir"] = sortDir;
parameters["filter"] = filter;
parameters["page"] = page;
parameters["top_el_id"] = top_el_id;
commonCls.sendView(top_el_id, parameters);
if((parent_el_id != null && receiver_list != null) && (top_el_id != parent_el_id)){
var parameters = new Object();
parameters["action"] = "pm_view_main_message_detail";
parameters["prefix_id_name"] = "popup_pm_message_detail" + receiver_list;
parameters["receiver_id"] = receiver_list;
parameters["page"] = this.page;
parameters["filter"] = this.filterFlag;
parameters["location"] = location;
parameters["mailbox"] = mailbox;
parameters["search_flag"] = search_flag;
parameters["theme_name"] = "system";
parameters["top_id_name"] = top_el_id;
parameters["parent_id_name"] = parent_el_id;
parameters["top_el_id"] = parent_el_id.replace("_", "");
commonCls.sendView(parent_el_id, parameters);
}
}.bind(this);
commonCls.sendPost(this.id, post, params);
},
operation: function(form_el,op, top_id_name, parent_id_name, mailbox,filter,page,location,eventElement,confirm_msg, list_total_count) {
if(this.id == "" || this.id == null) {
this.id = parent_id_name;
}
var params = new Object();
this.mailbox = mailbox;
this.page = page;
this.filterFlag = filter;
var search_flag = "none";
if(form_el.search_flag != null){ search_flag = form_el.search_flag.value; }
var select_all_flag = null;
if(form_el.select_all_flag != null){ select_all_flag = form_el.select_all_flag.value; }
var main_window_action = "pm_view_main_init";
if(search_flag == 'search'){
main_window_action = "pm_view_main_search_result";
this.mailbox = 4;
}
var receiver_list = "";
var receiver_id = "";
var receiver_cnt = 0;
if (form_el.receiver_id != null) {
receiver_id = form_el.receiver_id.value;
receiver_cnt++;
} else {
var inps = document.getElementsByName("receiver_id[]");
receiver_list = "";
for (var i=0; i<inps.length; i++) {
if (inps[i].checked==true){
if (receiver_list == "") {
receiver_list = inps[i].value;
} else {
receiver_list = receiver_list + "," + inps[i].value;
}
receiver_cnt++;
}
}
}
if (receiver_list != "" || receiver_id != "") {
if (op != "") {
if(op == "delete"){
if(receiver_id == ""){
if(form_el.select_all_flag != null && form_el.select_all_flag.value == "1"){
var receiver_cnt = list_total_count;
}
}
if(!window.confirm(confirm_msg.replace("%s", receiver_cnt))){
return false;
}
}
if (op == "newtag") {
if (receiver_list == "") {
this.showTagPopup("", receiver_id, eventElement, receiver_id, search_flag, select_all_flag,top_id_name, parent_id_name);
} else if (receiver_id == "") {
this.showTagPopup("", "", eventElement, receiver_list,search_flag, select_all_flag, this.id);
}
} else {
params["callbackfunc"] = function(res){
if (receiver_list != "") {
var parameters = new Object();
parameters["action"] = main_window_action;
parameters["sort_col"] = this.sortCol;
parameters["sort_dir"] = this.sortDir;
parameters["filter"] = this.filterFlag;
parameters["receiver_list"] = receiver_list;
parameters["mailbox"] = this.mailbox;
parameters["page"] = this.page;
if(main_window_action == 'pm_view_main_search_result'){
parameters["search_flag"] = search_flag;
}
commonCls.sendView(this.id, parameters);
}
if (receiver_id != "") {
var parameters = new Object();
parameters["action"] = main_window_action;
parameters["sort_col"] = this.sortCol;
parameters["sort_dir"] = this.sortDir;
parameters["filter"] = this.filterFlag;
parameters["mailbox"] = this.mailbox;
parameters["page"] = this.page;
if(main_window_action == 'pm_view_main_search_result'){
parameters["search_flag"] = search_flag;
}
if(parent_id_name == "_active_center_0") {
parameters["top_el_id"] = parent_id_name.replace("_", "");
commonCls.sendView(parent_id_name, parameters);
}else {
commonCls.sendView(top_id_name, parameters);
}
if(this.parent_id != null) {
if (op == "delete" && parent_id_name != "_active_center_0") {
commonCls.removeBlock(parent_id_name);
}else if(op != "delete"){
parameters = new Object();
parameters["action"] = "pm_view_main_message_detail";
parameters["theme_name"] = "system";
parameters["prefix_id_name"] = "popup_pm_message_detail" + receiver_id;
parameters["receiver_id"] = receiver_id;
parameters["page"] = this.page;
parameters["filter"] = this.filterFlag;
parameters["location"] = location;
parameters["mailbox"] = this.mailbox;
parameters["search_flag"] = search_flag;
parameters["top_id_name"] = top_id_name;
parameters["parent_id_name"] = parent_id_name;
parameters["top_el_id"] = parent_id_name.replace("_", "");
commonCls.sendView(parent_id_name, parameters);
}
}
}
}.bind(this);
var post = Form.serialize(form_el);
post += "&op=";
post += op;
commonCls.sendPost(this.id, post, params);
}
}
}
$("otherOp" + this.id).getElementsByTagName("option")[0].selected = true;
},
changeBox: function(mailbox, action, active_center) {
if(active_center == null){
active_center = false;
}
this.filterFlag = "";
this.mailbox = mailbox;
this.sortCol = null;
this.sortDir = "DESC";
if(active_center == false){
var parameters = new Object();
parameters["action"] = action;
parameters["mailbox"] = this.mailbox;
commonCls.sendView(this.id, parameters);
}
},
update:function(mailbox, search_flag) {
this.filterFlag = "";
this.mailbox = mailbox;
var parameters = new Object();
var action = "pm_view_main_init";
if(search_flag == 'search'){
action = "pm_view_main_search_result";
this.mailbox = 4;
}
parameters["action"] = action;
parameters["mailbox"] = this.mailbox;
parameters["search_flag"] = search_flag;
commonCls.sendView(this.id, parameters);
},
setSortState:function() {
var imgObj = $("pm_sort_img" + this.id + "_" + this.sortCol);
if(this.sortDir == "ASC") {
imgObj.src = this.ascImg;
} else {
imgObj.src = this.descImg;
}
commonCls.displayVisible(imgObj);
if(this.oldSortCol != null) {
var oldImgObj = $("pm_sort_img" + this.id + "_" + this.oldSortCol);
commonCls.displayNone(oldImgObj);
this.oldSortCol = null;
}
},
createSettingTabset: function(activeIndex, tab1_title, tab2_title, tab3_title){
if(!activeIndex) { activeIndex = 0; }
var top_el = $(this.id);
var tabset = new compTabset(top_el);
if(activeIndex == 0){
tabset.addTabset(tab1_title, null, null);
}else{
tabset.addTabset(tab1_title,
function(){ commonCls.sendView(this.id, 'pm_view_main_forward');  return false;}.bind(this),
null);
}
if(activeIndex == 1){
tabset.addTabset(tab2_title, null, null);
}else{
tabset.addTabset(tab2_title,
function(){ commonCls.sendView(this.id, 'pm_view_main_filter_init');  return false;}.bind(this),
null);
}
if(activeIndex == 2){
tabset.addTabset(tab3_title, null, null);
}else{
tabset.addTabset(tab3_title,
function(){ commonCls.sendView(this.id, 'pm_view_main_tag_init');  return false;}.bind(this),
null);
}
tabset.setActiveIndex(activeIndex);
tabset.render();
commonCls.focus($(this.id));
},
listRadioOnCicked: function(radio_el, row_el_id, receiver_id, cls_selected, cls_unselected){
if($(row_el_id)){
if(radio_el.checked == true){
$(row_el_id).className = cls_selected;
this.switchListColsClass(receiver_id, true);
}else{
$(row_el_id).className = cls_unselected;
this.switchListColsClass(receiver_id, false);
var select_all_checkbox_el = $("pm_form" + this.id + "_select_all_checkbox");
var select_all_flag_el = $("pm_form" + this.id + "_select_all_flag");
var select_all_message_el = $("pm" + this.id + "_select_all_message");
if(select_all_checkbox_el && select_all_flag_el && select_all_message_el){
select_all_checkbox_el.checked = false;
select_all_flag_el.value = "0";
commonCls.displayNone(select_all_message_el);
}
}
}
},
listRowOnRead: function(top_el_id, row_el_id){
Element.removeClassName($(row_el_id), "pm_list_inbox_unread");
var radio_el_id = row_el_id.replace('pm_list_row', 'pm_list_row_radio');
$(radio_el_id).onclick = function(event){
pmCls[top_el_id].listRadioOnCicked(this, row_el_id, row_el_id.replace('pm_list_row' + top_el_id + '_', ''), 'pm_list_inbox_checked', '');
};
},
saveMailForward: function(form_el){
var post = Form.serialize(form_el);
commonCls.sendPost(this.id, post);
},
showFilterPopup: function(filter_id, eventElement) {
commonCls.sendPopupView(eventElement, {'prefix_id_name':'popup_pm_filter_entry' + this.id, 'action':'pm_view_main_filter_entry', 'filter_id':filter_id, 'top_id_name':this.id}, {'top_el':$(this.id), 'modal_flag':true, 'center_flag':true});
},
enterFilter: function(form_el, top_el_id){
var params = new Object();
var post = Form.serialize(form_el);
params["callbackfunc"] = function(res){
commonCls.removeBlock(this.id);
var parameters = new Object();
parameters["action"] = "pm_view_main_filter_init";
commonCls.sendView(top_el_id, parameters);
}.bind(this);
commonCls.sendPost(this.id, post, params);
},
deleteFilter: function(filter_id, confirmMessage) {
if (!confirm(confirmMessage)) return false;
var params = new Object();
var post = {
"action":"pm_action_main_filter_delete",
"filter_id":filter_id
};
params["target_el"] = $(this.id);
params["callbackfunc"] = function(res){
}.bind(this);
commonCls.sendPost(this.id, post, params);
},
jumpMailBoxPage: function(form_el, mailbox, filter, page){
this.mailbox = mailbox;
this.filterFlag = filter;
this.page = page;
var top_el = $(this.id);
var params = new Object();
var search_flag = "none";
if(form_el.search_flag != null){ search_flag = form_el.search_flag.value; }
if(search_flag == 'search'){
var action = "pm_view_main_search_result";
}else{
var action = "pm_view_main_init";
}
params["param"] = {
"action":action,
"sort_col":this.sortCol,
"sort_dir":this.sortDir,
"filter":this.filterFlag,
"mailbox":this.mailbox,
"page":this.page
};
params["top_el"] = top_el;
params["loading_el"] = top_el;
params["target_el"] = top_el;
params["callbackfunc"] = function(res) {
this.setSortState();
}.bind(this);
commonCls.send(params);
},
addCC: function(lang_delete, value, user_lang, user_id){
var cc_el = $("pm" + this.id + "_addreceiver");
var user_show_flag = false;
if(user_id != null && value != null){
user_show_flag = true;
var user_link = '<a class="syslink" href="#" title="' + user_lang.replace("%s", value) + '" onclick="commonCls.showUserDetail(event, \'' + user_id + '\');return false;">' + value + '</a>';
}
var li = document.createElement('li');
li.id = "pm" + this.id + "_cc_" + this.ccIndex;
cc_el.appendChild(li);
li.className = "pm_ins_subject_li pm_ins_subject_cc";
if(user_show_flag){
li.innerHTML = '<div class="pm_ins_cc_left_input"><input id="pm_form' + this.id + '_receivers' + this.ccIndex + '" class="pm_ins_subject_li_input" type="text" name="receivers[]" value="' + value + '" onkeyup="pmCls[\'' + this.id + '\'].enterCC(' + this.ccIndex + '); return false;" onblur="pmCls[\'' + this.id + '\'].renderCC(' + this.ccIndex + ',\'' + user_lang + '\', true)"/></div><div id="pm_form' + this.id + '_cc_user' + this.ccIndex + '" class="pm_ins_cc_left">' + user_link + '</div><div id="pm_form' + this.id + '_cc_line' + this.ccIndex + '" class="pm_ins_cc_center">&nbsp;|&nbsp;</div><div id="pm_form' + this.id + '_cc_delete' + this.ccIndex + '" class="pm_ins_cc_right display-block"><a href="#" onclick="pmCls[\'' + this.id + '\'].removeCC(' + this.ccIndex + '); return false;">' + lang_delete + '</a></div>';
}else{
value = value.replace('&nbsp;', ' ');
li.innerHTML = '<div class="pm_ins_cc_left_input"><input id="pm_form' + this.id + '_receivers' + this.ccIndex + '" class="pm_ins_subject_li_input" type="text" name="receivers[]" value="' + value + '" onkeyup="pmCls[\'' + this.id + '\'].enterCC(' + this.ccIndex + '); return false;" onblur="pmCls[\'' + this.id + '\'].renderCC(' + this.ccIndex + ',\'' + user_lang + '\', true)"/></div><div id="pm_form' + this.id + '_cc_user' + this.ccIndex + '" class="pm_ins_cc_left display-none"></div><div id="pm_form' + this.id + '_cc_line' + this.ccIndex + '" class="pm_ins_cc_center display-none">&nbsp;|&nbsp;</div><div id="pm_form' + this.id + '_cc_delete' + this.ccIndex + '" class="pm_ins_cc_right display-block"><a href="#" onclick="pmCls[\'' + this.id + '\'].removeCC(' + this.ccIndex + '); return false;">' + lang_delete + '</a></div>';
}
this.ccIndex++;
},
removeCC: function(cc_id){
this.enterCC(cc_id);
var cc_el = document.getElementById("pm" + this.id + "_addreceiver");
var li = document.getElementById("pm" + this.id + "_cc_" + cc_id);
cc_el.removeChild(li);
},
enterCC: function(cc_id){
if(cc_id == ''){
var receivers_el = $('pm_form' + this.id + '_receivers');
var receiver_user_el = $('pm_form' + this.id + '_receiver_user');
if(receivers_el.value != ''){
receiver_user_el.innerHTML = receivers_el.value;
commonCls.displayVisible(receiver_user_el);
}else{
receiver_user_el.innerHTML = '';
commonCls.displayNone(receiver_user_el);
}
}else{
var cc_el = $('pm_form' + this.id + '_receivers' + cc_id);
var cc_user_el = $('pm_form' + this.id + '_cc_user' + cc_id);
var cc_line_el = $('pm_form' + this.id + '_cc_line' + cc_id);
var cc_delete_el = $('pm_form' + this.id + '_cc_delete' + cc_id);
if(cc_el.value != ''){
cc_user_el.innerHTML = cc_el.value;
commonCls.displayVisible(cc_user_el);
commonCls.displayVisible(cc_line_el);
commonCls.displayVisible(cc_delete_el);
}else{
cc_user_el.innerHTML = '';
commonCls.displayNone(cc_user_el);
commonCls.displayNone(cc_line_el);
}
}
},
renderCC: function(cc_id, user_lang, error_report){
if(cc_id == ''){
var input_el = $('pm_form' + this.id + '_receivers');
var label_el = $('pm_form' + this.id + '_receiver_user');
var avatar_el = $('pm_form' + this.id + '_avatar');
}else{
var input_el = $('pm_form' + this.id + '_receivers' + cc_id);
var label_el = $('pm_form' + this.id + '_cc_user' + cc_id);
var avatar_el = null;
}
if(input_el){
var top_el = $(this.id);
var params = new Object();
params["param"] = {
"action":"pm_action_main_userinfo",
"handle":input_el.value,
"flag": "info"
};
params["method"] = "post";
params["top_el"] = top_el;
params["callbackfunc_error"] = function(res) {
var handle = input_el.value.escapeHTML();
res = res.split("|");
if(res[0] == 'false'){
var send_all_flag_el = $('pm_form' + this.id + '_send_all_flag');
if(send_all_flag_el != null){
if(send_all_flag_el.checked == 1){
error_report = false;
}
}
if(error_report){
window.alert(res[1].replace('<br />', ''));
}
label_el.innerHTML = '<span class="pm_error">' + handle + '</span>';
}else{
if(res[0] == 'true'){
var user_id = res[1].replace('<br />', '');
var html = '<a class="syslink" href="#" title="' + user_lang.replace("%s", handle) + '" ';
html += 'onclick="commonCls.showUserDetail(event, \'' + user_id + '\');return false;">';
html += handle + '</a>';
label_el.innerHTML = html;
if(avatar_el != null){
this.loadingAvatar(avatar_el.id, user_id);
}
}
}
}.bind(this);
commonCls.send(params);
}
},
searchMessage: function(form_el){
var params = new Object();
params["target_el"] = $(this.id);
params["callbackfunc"] = function(res){
this.mailbox = "0";
}.bind(this);
var post = Form.serialize(form_el);
commonCls.sendPost(this.id, post, params);
},
onClickAllDown: function(select_all_flag_id, clicker_id, msg_canvas_id,
msg_selected, msg_selected_link,
msg_unselected, msg_unselected_link){
if(!$(select_all_flag_id)){
return false;
}
if(!$(clicker_id)){
return false;
}
if(!$(msg_canvas_id)){
return false;
}
if($(clicker_id).checked == true){
var msg_canvas_el = $(msg_canvas_id);
var select_all_flag_el = $(select_all_flag_id);
commonCls.displayVisible(msg_canvas_el);
select_all_flag_el.value = "0";
msg_selected_link = '<br/><a href="#" onclick="pmCls[\'' + this.id + '\'].onSelectAll(\'' + select_all_flag_id + '\', \'' + clicker_id + '\', \'' + msg_canvas_id + '\', \'' + msg_unselected + '\', \'' + msg_unselected_link + '\'); return false;">' + msg_selected_link + '</a>';
msg_canvas_el.innerHTML = msg_selected + msg_selected_link;
this.selectAllListRows();
}else{
var msg_canvas_el = $(msg_canvas_id);
var select_all_flag_el = $(select_all_flag_id);
select_all_flag_el.value = "0";
commonCls.displayNone(msg_canvas_el);
this.unSelectAllListRows();
}
},
onSelectAll: function(select_all_flag_id, clicker_id, msg_canvas_id,
msg_unselected, msg_unselected_link){
if(!$(select_all_flag_id)){
return false;
}
if(!$(clicker_id)){
return false;
}
if(!$(msg_canvas_id)){
return false;
}
$(select_all_flag_id).value = "1";
msg_unselected_link = '<br/><a href="#" onclick="pmCls[\'' + this.id + '\'].onUnselectAll(\'' + select_all_flag_id + '\', \'' + clicker_id + '\', \'' + msg_canvas_id + '\'); return false;">' + msg_unselected_link + '</a>';
$(msg_canvas_id).innerHTML = msg_unselected + msg_unselected_link;
},
onUnselectAll: function(select_all_flag_id, clicker_id, msg_canvas_id){
if(!$(select_all_flag_id)){
return false;
}
if(!$(clicker_id)){
return false;
}
if(!$(msg_canvas_id)){
return false;
}
var clicker_el = $(clicker_id);
var msg_canvas_el = $(msg_canvas_id);
var select_all_flag_el = $(select_all_flag_id);
clicker_el.checked = false;
commonCls.displayNone(msg_canvas_el);
select_all_flag_el.value = "0";
msg_canvas_el.innerHTML = "";
this.unSelectAllListRows();
},
selectAllListRows: function(){
var radios = document.getElementsByName("receiver_id[]");
for(var i = 0; i < radios.length; i++){
radios[i].checked = true;
var tableRow = $("pm_list_row" + this.id + "_" + radios[i].value);
if(tableRow){
if(tableRow.className != ""){
tableRow.className = tableRow.className + " pm_list_inbox_checked";
}else{
tableRow.className = "pm_list_inbox_checked";
}
}
this.switchListColsClass(radios[i].value, true);
}
},
unSelectAllListRows: function(){
var radios = document.getElementsByName("receiver_id[]");
for(var i = 0; i < radios.length; i++){
radios[i].checked = false;
var tableRow = $("pm_list_row" + this.id + "_" + radios[i].value);
if(tableRow){
if(tableRow.className != ""){
Element.removeClassName(tableRow, "pm_list_inbox_checked");
}
}
this.switchListColsClass(radios[i].value, false);
}
},
switchListColsClass: function(receiver_id, select_flag){
var col1_id = "pm_list_col" + this.id + "_" + receiver_id + "_check";
var col2_id = "pm_list_col" + this.id + "_" + receiver_id + "_sender";
var col3_id = "pm_list_col" + this.id + "_" + receiver_id + "_subject";
var col4_id = "pm_list_col" + this.id + "_" + receiver_id + "_date";
if( $(col1_id) && $(col2_id) && $(col3_id) && $(col4_id) ){
if(select_flag){
$(col1_id).className = $(col1_id).className.replace('pm_list_inbox_td', 'pm_list_inbox_checked_td');
$(col2_id).className = $(col2_id).className.replace('pm_list_inbox_td', 'pm_list_inbox_checked_td');
$(col3_id).className = $(col3_id).className.replace('pm_list_inbox_td', 'pm_list_inbox_checked_td');
$(col4_id).className = $(col4_id).className.replace('pm_list_inbox_td', 'pm_list_inbox_checked_td');
}else{
$(col1_id).className = $(col1_id).className.replace('pm_list_inbox_checked_td', 'pm_list_inbox_td');
$(col2_id).className = $(col2_id).className.replace('pm_list_inbox_checked_td', 'pm_list_inbox_td');
$(col3_id).className = $(col3_id).className.replace('pm_list_inbox_checked_td', 'pm_list_inbox_td');
$(col4_id).className = $(col4_id).className.replace('pm_list_inbox_checked_td', 'pm_list_inbox_td');
}
}
},
sendToAllMember: function(sent_all_flag_el){
var send_normal_el = $("pm_form" +  this.id + "_send_normal");
var hidden = false;
if(sent_all_flag_el.checked == true){
if(send_normal_el != null){
commonCls.displayNone(send_normal_el);
}
hidden = true;
}else{
if(send_normal_el != null){
commonCls.displayVisible(send_normal_el);
}
hidden = false;
}
this.updAvatarBox(hidden);
},
updAvatarBox: function(hidden_flag){
var avatar_el = $("pm_form" +  this.id + "_avatar");
if(hidden_flag == true){
if(avatar_el != null){
this.backup_avatar_html = avatar_el.innerHTML;
avatar_el.innerHTML = "";
}
}else{
if(avatar_el != null){
avatar_el.innerHTML = this.backup_avatar_html;
}
}
},
sendMail: function() {
var params = new Object();
params["param"] = "pm_action_main_mail";
params["method"] = "post";
params["top_el"] = $(this.id);
commonCls.send(params);
},
loadingAvatar: function(avatar_id, user_id){
var top_el = $(this.id);
var params = new Object();
params["param"] = {
"action":"pm_action_main_userinfo",
"user_id":user_id,
"flag": "avatar"
};
params["method"] = "post";
params["top_el"] = top_el;
params["callbackfunc_error"] = function(res) {
var avatar_el = $(avatar_id);
res = res.replace('<br />', '');
if(res == "false"){
res = "";
}
if(avatar_el != null){
if(res == ""){
res = "/images/common/avatar_thumbnail.gif";
avatar_el.innerHTML = '<img src="' + _nc_base_url + res + '"/>';
}else{
avatar_el.innerHTML = '<img src="' + _nc_base_url + _nc_index_file_name + res + '&amp;thumbnail_flag=1"/>';
}
}
}.bind(this);
commonCls.send(params);
},
showSearchUser: function(event, lang_delete, user_lang) {
var params = new Object();
params["action"] = "pm_view_main_search_user";
params["prefix_id_name"] = "pm";
params["block_id"] = 0;
var popupParams = new Object();
var top_el = $(this.id);
popupParams['top_el'] = top_el;
popupParams['target_el'] = top_el;
popupParams['center_flag'] = true;
popupParams["modal_flag"] = true;
popupParams["callbackfunc"] = function(){
if(!pmCls['_pm_0']) {
pmCls['_pm_0'] = new clsPm('_pm_0','');
}
pmCls['_pm_0'].selectUserCallback = function(element){
this.selectUser(element, lang_delete, user_lang);
}.bind(this);
}.bind(this);
commonCls.sendPopupView(event, params, popupParams);
},
select: function(user_id) {
var element = $(user_id + this.id);
this.selectUserCallback(element);
},
selectUser: function(element, lang_delete, user_lang){
var handleElement = element.getElementsByTagName("input")[1];
var main_receiver_el = $('pm_form' + this.id + '_receivers');
if(main_receiver_el.value == ""){
main_receiver_el.value = handleElement.value;
this.enterCC('');
this.renderCC('', user_lang, true);
}else{
this.addCC(lang_delete, handleElement.value.escapeHTML(), user_lang);
this.enterCC(this.ccIndex -1);
this.renderCC(this.ccIndex -1, user_lang, true);
}
}
}

