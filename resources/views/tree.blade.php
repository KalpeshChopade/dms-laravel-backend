<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        body {
            background-color: #eee;
        }

        .node circle {
            fill: #fff;
            stroke: steelblue;
            stroke-width: 3px;
        }

        .node text {
            font: 12px sans-serif;
            text-anchor: middle;
        }

        .link {
            fill: none;
            stroke: #ccc;
            stroke-width: 2px;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>

<body>

    <script src="https://d3js.org/d3.v5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/6.25.0/babel.min.js"></script>
    <script>
        const treeDataOriginal = {
            "name": "Eve",
            "value": 15,
            "type": "black",
            "level": "yellow",
            "children": [{
                    "name": "Cain",
                    "value": 10,
                    "type": "grey",
                    "level": "red"
                },
                {
                    "name": "Seth",
                    "value": 10,
                    "type": "grey",
                    "level": "red",
                    "children": [{
                            "name": "Enos",
                            "value": 7.5,
                            "type": "grey",
                            "level": "purple"
                        },
                        {
                            "name": "Noam",
                            "value": 7.5,
                            "type": "grey",
                            "level": "purple"
                        }
                    ]
                },
                {
                    "name": "Abel",
                    "value": 10,
                    "type": "grey",
                    "level": "blue"
                },
                {
                    "name": "Awan",
                    "value": 10,
                    "type": "grey",
                    "level": "green",
                    "children": [{
                        "name": "Enoch",
                        "value": 7.5,
                        "type": "grey",
                        "level": "orange"
                    }]
                },
                {
                    "name": "Azura",
                    "value": 10,
                    "type": "grey",
                    "level": "green"
                }
            ]
        };


        var settings = {
            "url": "/api/v1/get-my-hierarchy?user_id=3",
            "method": "GET",
            "timeout": 0,
        };

        $.ajax(settings).done(function(response) {
            console.log(response);
            generateTree(response);
        });



        function generateTree(responseData) {

            // set the dimensions and margins of the diagram
            const margin = {
                    top: 20,
                    right: 90,
                    bottom: 30,
                    left: 90
                },
                width = 1500 - margin.left - margin.right,
                // height = 700 - margin.top - margin.bottom;
                height = 400;

            // declares a tree layout and assigns the size
            const treemap = d3.tree().size([width, height]);

            // assigns the data to a hierarchy using parent-child relationships
            const nodes = d3.hierarchy(responseData.data, d => d.children);

            // maps the node data to the tree layout
            const treeData = treemap(nodes);

            // append the svg object to the body of the page
            // appends a 'group' element to 'svg'
            // moves the 'group' element to the top left margin
            const svg = d3.select("body").append("svg")
                .attr("width", width + margin.left + margin.right)
                .attr("height", height + margin.top + margin.bottom),
                g = svg.append("g")
                .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

            // adds the links between the nodes
            const link = g.selectAll(".link")
                .data(treeData.descendants().slice(1))
                .enter().append("path")
                .attr("class", "link")
                .style("stroke", "#ccc")
                .attr("d", d => {
                    return "M" + d.x + "," + d.y +
                        "C" + (d.x + d.parent.x) / 2 + "," + d.y +
                        " " + (d.x + d.parent.x) / 2 + "," + d.parent.y +
                        " " + d.parent.x + "," + d.parent.y;
                });

            // adds each node as a group
            const node = g.selectAll(".node")
                .data(treeData.descendants())
                .enter().append("g")
                .attr("class", d => "node" + (d.children ? " node--internal" : " node--leaf"))
                .attr("transform", d => "translate(" + d.x + "," + d.y + ")")
                .on("click", function(d) {
                    if (d.data.user_id === 0) {
                        console.log("Parent user_id:", d.parent.data.user_id);
                        invite(d.parent.data.user_id);
                    }
                });

            // adds the circle to the node
            node.append("circle")
                .attr("r", 10)
                .style("stroke", "steelblue")
                .style("fill", "#fff")
                .attr("cursor", "pointer"); // Add cursor:pointer to indicate clickable

            // adds the text to the node
            node.append("text")
                .attr("dy", ".35em")
                .text(d => (d.data.user_id === 0) ? "+" : d.data.user_id)

        }

        function invite(parent_id) {
            alert(parent_id);
        }
    </script>

</body>

</html>
