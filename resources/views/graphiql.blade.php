<!DOCTYPE html>
<html>

<head>
    <title>GraphiQL - Route Schedule Service</title>
    <link rel="stylesheet" href="https://unpkg.com/graphiql@3/graphiql.min.css" />
    <style>
        body {
            height: 100vh;
            margin: 0;
        }

        #graphiql {
            height: 100vh;
        }
    </style>
</head>

<body>
    <div id="graphiql">Loading...</div>

    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/graphiql@3/graphiql.min.js"></script>

    <script>
        const fetcher = GraphiQL.createFetcher({
            url: '/graphql',
        });

        const root = ReactDOM.createRoot(document.getElementById('graphiql'));
        root.render(
            React.createElement(GraphiQL, { fetcher: fetcher }),
        );
    </script>
</body>

</html>