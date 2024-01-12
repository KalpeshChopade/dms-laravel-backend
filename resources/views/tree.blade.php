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
                stroke-width: 1px;
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

            .node--green circle {
                fill: #8BC34A;
            }

            .node--sky-blue circle {
                fill: #87CEEB;
            }

            .node--navy-blue circle {
                fill: #000080;
            }
        </style>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
            integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </head>

    <body>

        <div id="body"></div>

        <script src="https://d3js.org/d3.v5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/6.25.0/babel.min.js"></script>
        <script>
            var settings = {
                "url": "/api/v1/get-my-hierarchy?user_id=1",
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
                const svg = d3.select("#body").append("svg")
                    .attr("width", width + margin.left + margin.right)
                    .attr("height", height + margin.top + margin.bottom),
                    g = svg.append("g")
                    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

                // adds the links between the nodes
                const link = g.selectAll(".link")
                    .data(treeData.links())
                    .enter().append("path")
                    .attr("class", "link")
                    .style("stroke", "#ccc")
                    .attr("d", d => {
                        return "M" + d.source.x + "," + d.source.y +
                            "C" + (d.source.x + d.target.x) / 2 + "," + d.source.y +
                            " " + (d.source.x + d.target.x) / 2 + "," + d.target.y +
                            " " + d.target.x + "," + d.target.y;
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
                    .style("fill", d => getFillColor(d).fillColor)
                    .attr("cursor", "pointer"); // Add cursor:pointer to indicate clickable

                // adds the text to the node
                node.append("text")
                    .attr("dy", ".35em")
                    .text(d => (d.data.user_id === 0) ? "+" : d.data.user_id)
                    .style("fill", d => getFillColor(d).textColor)
            }

            function invite(parent_id) {
                alert(parent_id);
            }

            function hasThreeChildren(node) {
                return node.children && node.children.length === 3 && node.children.every(child => child.data.user_id !== 0);
            }

            const getFillColor = (node) => {
                if (node.children && node.children.length === 3 && node.children.every(child => child.data.user_id === 0)) {
                    return {
                        fillColor: "#87CEEB",
                        textColor: "#000"
                    }; // Sky Blue, Black
                } else if (node.children && node.children.length < 3 && node.children.every(child => child.data.user_id !==
                        0)) {
                    return {
                        fillColor: "#8BC34A",
                        textColor: "#000"
                    }; // Green, Black
                } else if (hasThreeChildren(node) && node.children.every(child => child.data.user_id !== 0)) {
                    return {
                        fillColor: "#000080",
                        textColor: "#fff"
                    }; // Navy Blue, White
                } else if (node.data.user_id !== 0) {
                    return {
                        fillColor: "#8BC34A",
                        textColor: "#000"
                    }; // Green, Black
                } else {
                    return {
                        fillColor: "#fff",
                        textColor: "#000"
                    }; // White, Black
                }
            };
        </script>

    </body>

    </html>
