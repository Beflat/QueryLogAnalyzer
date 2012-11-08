

/**
 * Itemの重なり合いをCanvasに描画するオブジェクト
 */
OverlapView = function(id) {
	this.init.apply(this, arguments);
}
OverlapView.prototype = {
	
	//定数
	GRAPH_PADDING_LEFT:   20,
	GRAPH_PADDING_TOP:    20,
	GRAPH_PADDING_RIGHT:  20,
	GRAPH_PADDING_BOTTOM: 20,
	
	//キャンバスの状態
	STATE_NORMAL: 0,
	STATE_DRAG_SCROLL: 1,
	
	//メンバ変数
	id: null,
	width : 0,
	height: 0,
	minTime: 0,
	maxTime: 0,
	renderFrom: 0,
	renderTo: 0,
	items: [],
	state: 0,
	
	//プライベートメンバ
	_prevMouseX: 0,
	
	/**
	 * コンストラクタ
	 */
	init: function(id) {
		this.id = id;
		this.state = this.STATE_NORMAL;
		
		var self = this;
		var canvasElement = document.getElementById(this.id);
		canvasElement.addEventListener('mousemove',  function(e) { return self._onMouseMove(e);}, false);
		canvasElement.addEventListener('mousedown',  function(e) { return self._onMouseDown(e);}, false);
		canvasElement.addEventListener('mouseup',    function(e) { return self._onMouseUp(e);},   false);
		canvasElement.addEventListener('DOMMouseScroll', function(e) { return self._onWheel(e);},     false);
		canvasElement.addEventListener('mousewheel', function(e) { return self._onWheel(e);},     false);
		this.width = canvasElement.width;
		this.height = canvasElement.height;
		
		var minDate = new Date('2037-12-31');
		this.minTime = minDate.getTime() / 1000;
		this.maxTime = 0;
		this.renderFrom = 0;
		this.renderTo = 0;
		this.items = [];
		
		this._prevMouseX = 0;
	},
	setRenderRange: function(from, to) {
		this.renderFrom = from;
		this.renderTo = to;
	},
	
	addItem: function(item) {
		
		var self = this;
		item.onSelect = function(i) {
			return self.onItemSelectChange(i);
		}
		
		this.items.push(item);
		if(item.endTime > this.maxTime) {
			this.maxTime = item.endTime;
		}
		if(item.startTime < this.minTime) {
			this.minTime = item.startTime;
		}
	},
	
	/**
	 * キャンバス全体の描画
	 */
	render: function() {
		
		var canvasElement = document.getElementById(this.id);
		var context = canvasElement.getContext('2d');
		
		context.clearRect(0, 0, this.width, this.height);
		
		//枠の描画
		this._renderBorder(context);
		this._renderGraphBorder(context);
		
		//Itemの描画
		renderingContext = new RenderingContext(context, this.renderFrom, this.renderTo);
		renderingContext.setClientRect(this.GRAPH_PADDING_LEFT, this.GRAPH_PADDING_TOP, 
			this.width - this.GRAPH_PADDING_RIGHT - this.GRAPH_PADDING_LEFT, 
			this.height - this.GRAPH_PADDING_BOTTOM - this.GRAPH_PADDING_TOP);
		
		//軸の描画
		axisRenderer = new DefaultAxisRenderer();
		axisRenderer.render(renderingContext);
		
		for(i in this.items) {
			if(!this.items[i].isRenderTarget(this.renderFrom, this.renderTo)) {
				continue;
			}
			this.items[i].render(renderingContext);
			renderingContext.registerItem(this.items[i]);
		}
	},
	
	/**
	 * アイテムの選択が変化した。
	 */
	onItemSelectChange: function(item) {
	},
	
	/**
	 * Canvasの枠部分を描画する。
	 */
	_renderBorder: function(context) {
		context.beginPath();
		context.lineWidth=1;
		context.strokeStyle='#000';
		context.strokeRect(0, 0, this.width, this.height);
	},
	
	/**
	 * グラフ部分の枠を描画する。
	 */
	_renderGraphBorder: function(context) {
		context.beginPath();
		context.lineWidth=1;
		context.strokeStyle='#000';
		context.strokeRect(this.GRAPH_PADDING_LEFT, this.GRAPH_PADDING_TOP, 
			this.width - this.GRAPH_PADDING_RIGHT - this.GRAPH_PADDING_LEFT, 
			this.height - this.GRAPH_PADDING_BOTTOM - this.GRAPH_PADDING_BOTTOM);
	},
	
	/**
	 * マウス移動時のイベントハンドラ
	 */
	_onMouseMove: function(e) {
		
		if(this.state == this.STATE_NORMAL) {
			//通常時は配下のItemにイベントを伝搬する。
			this._prevMouseX = e.offsetX;
			this._onMouseMoveAroundItems(e.offsetX, e.offsetY);
			return false;
		}
		var length = this.renderTo - this.renderFrom;
		var velocity = (length / 25);
		if(this._prevMouseX > e.offsetX) {
			this._scroll(velocity);
		} else if(this._prevMouseX < e.offsetX) {
			this._scroll(-velocity);
		}
		this._prevMouseX = e.offsetX;
		this.render();
	},
	
	_scroll: function(velocity) {
		var length = this.renderTo - this.renderFrom;
		if(velocity > 0) {
			this.renderFrom = Math.min(this.renderFrom + velocity, this.maxTime - length);
			this.renderTo = this.renderFrom + length;
		} else {
			this.renderFrom = Math.max(this.renderFrom + velocity, this.minTime);
			this.renderTo = this.renderFrom + length;
		}
	},
	
	/**
	 * マウスボタン押下時のイベントハンドラ
	 */
	_onMouseDown: function(e) {
		this.state = this.STATE_DRAG_SCROLL;
	},
	
	/**
	 * マウスボタンを離した時のイベントハンドラ
	 */
	_onMouseUp: function(e) {
		this.state = this.STATE_NORMAL;
	},
	/**
	 * ホイール操作時のイベントハンドラ
	 */
	_onWheel: function(e) {
		var d = 0;
		if(e.wheelDelta) {
			//chrome
			d = e.wheelDelta;
		} else if(e.detail) {
			//firefox
			d = e.detail;
		}
		
		var length = this.renderTo - this.renderFrom;
		var velocity = length / 20;
		if(e.shiftKey) {
			//Shiftキーの場合はスクロール
			this._scroll((d > 0) ? -velocity : velocity);
		} else {
			//通常時はズーム
			if(d > 0) {
				this.renderTo = this.renderTo + 10;
			} else {
				this.renderTo = Math.max(this.renderTo - 10, this.renderFrom + 30);
			}
			
		}
		
		this.render();
	},
	
	/**
	 * 各Itemに対してmousemoveイベントを伝搬させる。
	 */
	_onMouseMoveAroundItems: function(mouseX, mouseY) {
		var item = 0;
		var pos = 0;
		for(i in this.items) {
			if(!this.items[i].isOverlapped(this.renderFrom, this.renderTo)) {
				continue;
			}
			
			var canvasSize = this.width - this.GRAPH_PADDING_LEFT - this.GRAPH_PADDING_RIGHT;
			var timeLength = this.renderTo - this.renderFrom;
			
			pos = ((mouseX - this.GRAPH_PADDING_LEFT) / canvasSize) * timeLength + this.renderFrom;
			
			if(!this.items[i].onMouseMove(pos, mouseY - this.GRAPH_PADDING_TOP)) {
				this.render();
				break;
			}
		}

	},
	
}

/**
 * Canvas上に描画する際に必要な情報を含んだオブジェクト。
 * レンダリングの始点/終点や、描画領域のサイズ、Canvasのコンテキスト、描画するアイテムの配列を持っている。
 * @param CanvasRenderingContext2D context
 */
RenderingContext = function(context, renderFrom, renderTo) {
	this.init.apply(this, arguments);
}
RenderingContext.prototype = {
	context: null,
	renderFrom: 0,
	renderTo: 0,
	baseX: 0,
	baseY: 0,
	baseW: 0,
	baseH: 0,
	items: [],
	init: function(context, renderFrom, renderTo) {
		this.context = context;
		this.renderFrom = renderFrom;
		this.renderTo = renderTo;
		this.baseX = 0;
		this.baseY = 0;
		this.baseW = 0;
		this.baseH = 0;
		this.items = [];
	},
	setClientRect: function(x, y, w, h) {
		this.baseX = x;
		this.baseY = y;
		this.baseW = w;
		this.baseH = h;
	},
	registerItem: function(item) {
		this.items.push(item);
	},
	
	/**
	 * 渡されたItemが何個のItemと座標上で重なっているかを返す。
	 */
	getOverlappedCount: function(item) {
		var overlapped = 0;
		var i = 0;
		for(i in this.items) {
			if(this.items[i] === item) {
				continue;
			}
			if(this.items[i].isOverlapped(item.startTime, item.endTime)) {
				overlapped++;
			}
		}
		return overlapped;
	},
}


/**
 * Canvas上に描画されるオブジェクト
 */
Item = function(id, startTime, endTime, data) {
	this.init.apply(this, arguments);
}
Item.prototype = {
	
	//定数
	STATE_NORMAL: 0,
	STATE_MOUSE_HOVER: 1,
		
	id: '',
	startTime: 0,
	endTime: 0,
	state: this.STATE_NORMAL,
	data: {},
	onSelect: null,
	
	_renderY: -1,
	
	init: function(id, startTime, endTime, data) {
		this.id = id;
		this.startTime = startTime;
		this.endTime = endTime;
		this.state = this.STATE_NORMAL;
		this.data = data;
		this.onSelect = null;
		this._renderY = -1;
		if(startTime > endTime) {
			this.endTime = startTime;
			this.startTime = endTime;
		}
		if(startTime == endTime) {
			this.endTime++;
		}
	},
	
	/**
	 * 1件のItemを描画
	 * @param {RenderingContext} renderingContext
	 */
	render: function(renderingContext) {
		var context = renderingContext.context;
		var baseX = renderingContext.baseX;
		var baseY = renderingContext.baseY;
		
		var timeLength = renderingContext.renderTo - renderingContext.renderFrom;
		var canvasSize = renderingContext.baseW;
		
		var relativeStartPos = this.startTime - renderingContext.renderFrom; 
		var relativeEndPos   = this.endTime   - renderingContext.renderFrom;
		
		var startX     = (relativeStartPos / timeLength) * canvasSize;
		var endX       = (relativeEndPos   / timeLength) * canvasSize;
		
		if(this._renderY == -1) {
			this._renderY = 10 + renderingContext.getOverlappedCount(this) * 10;
		}
		
		//クリッピング
		startX = Math.max(startX, 0);
		endX   = Math.min(endX, canvasSize);
		context.save();
		var offsetY = 0;
		context.beginPath();
		if(this.state == this.STATE_NORMAL) {
			context.shadowOffsetX = 1;
			context.shadowOffsetY = 1;
			context.shadowBlur = 2;
			context.shadowColor = 'rgba(0, 0, 0, 0.3)';			
			context.strokeStyle = '#3A8FFF';
			context.lineWidth = 4;
			offsetY = 0;
		} else if(this.state == this.STATE_MOUSE_HOVER) {
			context.shadowOffsetX = 2;
			context.shadowOffsetY = 2;
			context.shadowBlur = 2;
			context.shadowColor = 'rgba(0, 0, 0, 0.3)';			
			context.strokeStyle = '#265DA5';
			context.lineWidth = 4;
			offsetY = -1;
		}
		context.moveTo(renderingContext.baseX + startX, renderingContext.baseY + this._renderY + offsetY);
		context.lineTo(renderingContext.baseX + endX,   renderingContext.baseY + this._renderY + offsetY);
		context.stroke();
		
		context.restore();
	},
	
	getStartTimeString: function() {
		return this._getDateString(this.startTime);
	},
	getEndTimeString: function() {
		return this._getDateString(this.endTime);
	},
	getData: function(key) {
		if(!this.hasData(key)) {
			return null;
		}
		return this.data[key];
	},
	getDataOr: function(key, def) {
		if(!this.hasData(key)) {
			return def;
		}
		return this.data[key];
	},
	
	hasData: function(key) {
		if(this.data[key]) {
			return true;
		}
		return false;
	},
	
	getAllData: function() {
		return this.data;
	},
	
	/**
	 * このオブジェクトが描画の範囲内かどうかを返す。
	 */
	isRenderTarget: function(from, to) {
		if(this.startTime >= to) {
			return false;
		}
		if(this.endTime <= from) {
			return false;
		}
		
		return true;
	},
	
	/**
	 * このオブジェクトが指定されたItemと時間的に重なっているかを返す。
	 */
	isOverlapped: function(from, to) {
		if(this.startTime >= from && this.startTime <= to) {
			return true;
		}
		if(this.endTime >= from && this.startTime <= to) {
			return true;
		}
		return false;
	},
	
	/**
	 * @param int time マウス位置の時間(単位：秒)
	 * @param int mouseY マウスのY座標。Canvasからグラフ領域までのオフセットは減算済。
	 */
	onMouseMove: function(time, mouseY) {
		var newState = this.state;
		
		if(!this.isOverlapped(time, time)) {
			//マウスがX軸で重なっていない。
			newState = this.STATE_NORMAL;
		} else if(this._renderY != -1 && Math.abs(mouseY - this._renderY) <= 3) {
			//Y座標の差が3以内ならカーソルが触れていると判断する。
			newState = this.STATE_MOUSE_HOVER;
		} else {
			newState = this.STATE_NORMAL;
		}
		
		if(newState != this.state) {
			//状態が変化した場合、イベントを消化する。
			//後続の処理を防止するため、falseを返す。
			this.state = newState;
			if(this.state == this.STATE_MOUSE_HOVER && this.onSelect != null) {
				this.onSelect(this);
			}
			return false;
		}
		
		//状態の変化がなかった場合、後続の要素の処理を続行させるためtrueを返す。
		return true;
	},
	/**
	 * UnixTimeStamp -> 時間文字列への変換。
	 */
	_getDateString: function(timeStamp) {
		var date = new Date(timeStamp*1000);
		
		return [date.getHours(), date.getMinutes(), date.getSeconds()].join(':');
	}
	
}


/**
 * グラフ部分の軸、判例の描画を行うオブジェクト
 */
DefaultAxisRenderer = function() {
	
}
DefaultAxisRenderer.prototype = {
	
	
	render: function(renderingContext) {
		var context = renderingContext.context;
		
		
		var timeSize = renderingContext.renderTo - renderingContext.renderFrom;
		var canvasSize = renderingContext.baseW - renderingContext.baseX;
		
		var scale = 10;
		if((5 / timeSize) * canvasSize >= 50) {
			scale = 1;
		}
		else if((10 / timeSize) * canvasSize >= 50) {
			scale = 5;
		}
		else if((20 / timeSize) * canvasSize >= 50) {
			scale = 10;
		}
		else if((40 / timeSize) * canvasSize >= 50) {
			scale = 20;
		} else {
			scale = 30;
		}
		
		
		var from = scale - (renderingContext.renderFrom % scale);
		var to = timeSize;
		
		var legendPos = 0;
		var time = 0;
		var pos = 0, x = 0, y = renderingContext.baseY;
		for(time=from; time <= to; time+=scale) {
			pos = (time / timeSize) * canvasSize;
			x = renderingContext.baseX + pos;
			
			context.beginPath();
			if((renderingContext.renderFrom + time) % 60 == 0) {
				//凡例を描画するタイミング
				context.font = "10pt Arial";
				context.textAlign = 'center';
				legendPos = pos;
				context.fillText(this._getDateString(renderingContext.renderFrom + time), x, renderingContext.baseY-5);
				context.strokeStyle = '#444';
				context.lineWidth = 0.5;
				context.moveTo(x, y);
				context.lineTo(x, y + renderingContext.baseH);
				context.stroke();
			} else {
				//通常時
				for(dash = y; dash < y + renderingContext.baseH; dash+=10) {
					context.strokeStyle = '#aaa';
					context.lineWidth = 0.5;
					context.moveTo(x, dash);
					context.lineTo(x, Math.min(dash + 5, y + renderingContext.baseH));
				}
				context.stroke();
			}
			
			
		}
	},
		
	/**
	 * UnixTimeStamp -> 時間文字列への変換。
	 */
	_getDateString: function(timeStamp) {
		var date = new Date(timeStamp*1000);
		
		return [date.getHours(), date.getMinutes(), date.getSeconds()].join(':');
	}
}

function debugObj(obj) {
	var buffer = '';
	console.log("CLASS_NAME=" + obj.toString());
	for(i in obj) {
		buffer = '';
		buffer += '[' + typeof(obj.i) + '] ' + i;
		buffer += ': ';
		buffer += obj.i;
		
		console.log(buffer);
	}
}


function debugKeys(obj, keys) {
	var buffer = '';
	var val = '';
	for(i in keys) {
		val = keys[i];
		buffer = '';
		buffer += '[' + typeof(obj[val]) + '] ' + val;
		buffer += ': ' + obj[val];
		
		console.log(buffer);
	}
}
