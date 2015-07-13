<html>
<head>
	<title>框架测试页面内容</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
</head>
<body>
	<div style="margin 100px auto"><h2>{-$val-}</h2></div>
	<div style="margin 100px auto"><h2>{-if (1 == 1)-}IF 语句的测试1{-/if-}</h2></div>
	<div style="margin 100px auto"><h2>{-if (1 == 2)-}IF 语句的测试2{-/if-}</h2></div>
	<div style="margin 100px auto">
		<h2>
			<h1>第一种循环例子</h1>
			{-foreach ($tarr as $t)-}
			{-$t-}<br/>
			{-/foreach-}
			<h1>第二种循环例子</h1>
			{-loop ($tarr as $t) -}
			{-$t-}<br/>
			{-/loop-}
		</h2>
	</div>
</body>
</html>
