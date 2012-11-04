
test( "Item: 初期化", function() {
	item = new Item('aa', 100, 200);
	ok(item.startTime == 100, '開始時間');
	ok(item.endTime == 200, '終了時間');
	
	item = new Item('aa', 200, 100);
	ok(item.startTime == 100, '開始時間(開始と終了を間違って指定した場合)');
	ok(item.endTime == 200, '終了時間(開始と終了を間違って指定した場合)');
});

test( "Item: 描画境界の確認", function() {
	item = new Item('aa', 100, 200);
	ok(item.isRenderTarget(200, 300) == false, 'アイテムが画面左側の外にある');
	ok(item.isRenderTarget(199, 300) == true,  'アイテムの左側が画面外');
	ok(item.isRenderTarget(100, 200) == true,  'アイテムが画面と同じサイズ');
	ok(item.isRenderTarget(99,  201) == true,  'アイテムが完全に画面内');
	ok(item.isRenderTarget(100, 199) == true,  'アイテムの右側が画面外');
	ok(item.isRenderTarget(50,  100) == false,  'アイテムが画面右側の外にある');
});

test("RenderingContext: 重複の確認", function() {
	a = new Item('aa', 100, 200);
	b = new Item('aa', 201, 300);
	c = new Item('aa', 301, 400);
	renderingContext = new RenderingContext(null, 100, 100);
	
	renderingContext.registerItem(a);
	renderingContext.registerItem(b);
	renderingContext.registerItem(c);
	
	ok(renderingContext.getOverlappedCount(a) == 0, 'a -> 誰とも重なり合っていない');
	ok(renderingContext.getOverlappedCount(b) == 0, 'b -> 誰とも重なり合っていない');
	ok(renderingContext.getOverlappedCount(c) == 0, 'c -> 誰とも重なり合っていない');
	
	a2 = new Item('aa', 100, 200);
	b2 = new Item('aa', 200, 300);
	c2 = new Item('aa', 301, 400);
	renderingContext2 = new RenderingContext(null, 100, 100);
	
	renderingContext2.registerItem(a2);
	renderingContext2.registerItem(b2);
	renderingContext2.registerItem(c2);
	
	ok(renderingContext2.getOverlappedCount(a2) == 1, 'a -> bと重なっている');
	ok(renderingContext2.getOverlappedCount(b2) == 1, 'b -> aと重なっている');
	ok(renderingContext2.getOverlappedCount(c2) == 0, 'c -> 誰とも重なり合っていない');
});