class Node {
    constructor(user, ...children) {
        this.user = user;
        this.children = children;
    }
}

function treeToHtml(tree) {
    class DisplayNode {
        constructor(node) {
            if (typeof node === "number") {
                this.width = node;
                return;
            }
            this.user = node.user;
            this.width = 1; // space at left. Excludes the width of the value-cell
            this.isRoot = true;
            this.hasLeftSibling = this.hasRightSibling = false;
            this.children = node.children.map(child => new DisplayNode(child));
            this.children.slice(1).forEach(child => child.hasLeftSibling = true);
            this.children.slice(0, -1).forEach(child => child.hasRightSibling = true);
            this.children.forEach(child => child.isRoot = false);
        }

        toString() {
            return `(${this.width})${this.user?.user_id ?? ""}`
        }

        toHtml() {
            if (!this.children) return ""; // This node represents right side padding
            const left = this.hasLeftSibling ? 'class="branch"' : '';
            const right = this.hasRightSibling ? 'class="branch"' : '';
            return `${this.width > 0 ? `<td colspan="${this.width}" ${left}></td>` : ""}
            <td><table>
                ${this.isRoot ? "" : `<tr><td ${left}></td><td ${right}></td></tr>`}
                <tr><td colspan="2">${this.user?.user_id}</td></tr>
                ${this.children.length ? "<tr><td></td><td></td></tr>" : ""}
            </table></td>`;
        }
    }

    const zip = (...arrays) =>
        Array.from({ length: Math.min(...arrays.map(({ length }) => length)) }, (_, i) =>
            arrays.map(array => array[i])
        );

    const getWidth = levels => levels[0].reduce((sum, { width, user }) => sum + width + (user?.user_id != null), 0);

    function mergePair(rowsA, rowsB) {
        const pair = [rowsA, rowsB];
        const rows = zip(...pair);
        const overlap = Math.min(...rows.map(([rowA, rowB]) => rowA.at(-1).width + rowB[0].width)) - 1;
        pair.forEach((rows, i) => {
            const indent = getWidth(rows) - overlap;
            if (indent < 0) rows.forEach(row => row.at(-i).width -= indent);
            if (indent > 0) pair[1 - i].slice(rows.length).forEach(row => row.at(-i).width += indent);
        });
        return rows.map(([rowA, rowB]) => [
            ...rowA.slice(0, -1),
            ((rowB[0].width += rowA.at(-1).width - overlap), rowB[0]),
            ...rowB.slice(1)
        ]).concat(rowsA.slice(rows.length), rowsB.slice(rows.length));
    }

    function treeToLevels(root) {
        if (!root.children.length) { // leaf: one level.
            return [[root, new DisplayNode(1)]];
        } else {
            const levels = root.children.map(treeToLevels).reduce(mergePair);
            const width = getWidth(levels); // guaranteed to be odd >= 3
            root.width = (width >> 1) | 1;
            return [[root, new DisplayNode(width - root.width - 1)], ...levels];
        }
    }

    if (!tree) return "";
    return treeToLevels(new DisplayNode(tree)).map(row =>
        `<tr>${row.map(node => node.toHtml()).join("")}</tr>`
    ).join("\n");
}

// ---------------- Example run -----------------
async function demo() {

    // Api Call here using jquery

    var settings = {
        "url": "http://127.0.0.1:8000/api/v1/get-my-hierarchy?user_id=1",
        "method": "GET",
        "timeout": 0,
      };

      $.ajax(settings).done(function (response) {
        console.log(response);
        const root = createTree(response.data);
        document.querySelector(".graph").innerHTML = treeToHtml(root);
      });
}

function createTree(data) {
    const buildTree = (item) => new Node(item, ...(item.children || []).map(buildTree));
    return buildTree(data);
}

demo();
