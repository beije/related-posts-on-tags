related-posts-on-tags
=====================

Wordpress plugin to fetch related posts based on tags.

Returns a list of posts that matches with tags that you've sent in through ajax, the results are ordered by weight. (One point per tag-match).

The Ajax url is available under:
http://www.example.dev/wp-admin/admin-ajax.php?action=search_on_tags&tags=["JSON","ARRAY",";)"]


Here's a ajax request made with jquery:

```javascript
    var url = "http://www.example.dev/wp-admin/admin-ajax.php";
    var getVars = { 
        action: "search_on_tags", 
        tags: JSON.stringify( [
            'tag 1',
            'tag 2',
            'tag 3'
        ] ),
        limit: 5,
        cats: JSON.stringify( [
            'category-slug-1',  
            'category-slug-2'
        ] )
    }
    
    $.get(  
        url,  
        getVars,  
        function( response ){
            console.log( response );  
        },  
        "json"  
    );  
```


You receive this as response:

```javascript
// Response map
[{
    date: "YYYY-MM-DD HH:MM:SS",    // String, Publish date
    image_full: [                   // Array, Original image (featured image)
        0:'http://',                // String, Url to image
        1:600,                      // Int, Image width
        2:200,                      // Int, Image height
        3:false                     // Boolean, Dunno, comes from wp_get_attachment_image_src()
    ],           
    image_large: Array[4],          // Array, Large image, see above for description
    image_medium: Array[4],         // Array, Medium image, see above for description
    image_small: Array[4],          // Array, Small image, see above for description
    position: 0,                    // Int, Position in matches    
    postid: 438,                    // Int, Post id
    timestamp: 1337893394,          // Int, Publish date as Unix timestamp
    title: "Post title",            // String, Post title
    url: "http://",                 // String, Permalink
    weight: 2                       // Int, Number of tag matches
}]
```