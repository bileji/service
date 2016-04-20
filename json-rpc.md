### Json rpc:

    -post {"jsonrpc":"2.0","method":"signIn","id":406150907,"params":{"username":"NE6YQuyb","password":"123456","platform": "web"}}
    
### Ab 测试:

    echo '{"jsonrpc":"2.0","method":"signIn","id":406150907,"params":{"username":"NE6YQuyb","password":"123456","platform": "web"}}' > ~/post.raw && ab -n 100 -c 10 -p ~/post.raw http://192.168.99.100/rpc/v1.0/reception/user