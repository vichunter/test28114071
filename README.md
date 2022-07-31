##Usage
docker-compose up

###API Endpoints
http://localhost:8403/api/posts/ (CRUD)
http://localhost:8403/api/comments/ (CRUD)

###Examples

####Add post
<pre>
POST http://localhost:8403/api/posts/ 
{
    "title": "some some title"
    "content": "some post content"
}
</pre>

####Add comment
<pre>
POST http://localhost:8403/api/comments/ 
{
    "post_id": &lt;post_id&gt;,
    "content": "some post content"
}
</pre>
