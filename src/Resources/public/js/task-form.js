document.addEventListener('DOMContentLoaded', function() {
    const nodesContainer = document.querySelector('.nodes-container');
    if (!nodesContainer) return;

    const nodesList = nodesContainer.querySelector('.nodes-list');
    const addButton = nodesContainer.querySelector('.add-node');
    const prototype = nodesList.dataset.prototype;
    let index = nodesList.children.length;

    // 添加节点
    addButton.addEventListener('click', function() {
        const newNode = createNode(prototype.replace(/__name__/g, index));
        nodesList.appendChild(newNode);
        index++;
    });

    // 删除节点
    nodesList.addEventListener('click', function(e) {
        if (e.target.matches('.remove-node')) {
            const nodeItem = e.target.closest('.node-item');
            nodeItem.remove();
            updateNodeTitles();
        }
    });

    // 拖拽排序
    initDragAndDrop();

    function createNode(prototype) {
        const div = document.createElement('div');
        div.className = 'node-item';
        div.innerHTML = `
            <div class="node-header">
                <span class="node-title">节点 #${index + 1}</span>
                <button type="button" class="btn btn-danger btn-sm remove-node">删除</button>
            </div>
            <div class="node-content">
                ${prototype}
            </div>
        `;
        return div;
    }

    function updateNodeTitles() {
        const nodes = nodesList.querySelectorAll('.node-item');
        nodes.forEach((node, i) => {
            node.querySelector('.node-title').textContent = `节点 #${i + 1}`;
        });
    }

    function initDragAndDrop() {
        const nodes = nodesList.querySelectorAll('.node-item');
        nodes.forEach(node => {
            node.setAttribute('draggable', true);
            node.addEventListener('dragstart', handleDragStart);
            node.addEventListener('dragend', handleDragEnd);
            node.addEventListener('dragover', handleDragOver);
            node.addEventListener('drop', handleDrop);
        });
    }

    function handleDragStart(e) {
        e.target.classList.add('dragging');
        e.dataTransfer.setData('text/plain', Array.from(nodesList.children).indexOf(e.target));
    }

    function handleDragEnd(e) {
        e.target.classList.remove('dragging');
    }

    function handleDragOver(e) {
        e.preventDefault();
    }

    function handleDrop(e) {
        e.preventDefault();
        const fromIndex = parseInt(e.dataTransfer.getData('text/plain'));
        const toIndex = Array.from(nodesList.children).indexOf(e.target.closest('.node-item'));
        
        if (fromIndex !== toIndex) {
            const nodes = Array.from(nodesList.children);
            const [movedNode] = nodes.splice(fromIndex, 1);
            nodes.splice(toIndex, 0, movedNode);
            nodesList.innerHTML = '';
            nodes.forEach(node => nodesList.appendChild(node));
            updateNodeTitles();
        }
    }
});
