<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title inertia> {{ config('app.name') }}</title>
		@viteReactRefresh
		@vite(['resources/js/app.tsx'])
		@inertiaHead
	</head>
	<body class="font-sans antialised bg-gray-50">
		@inertia
	</body>
</html>