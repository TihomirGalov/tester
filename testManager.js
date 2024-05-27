function createAndLoadTest() {
    fetch('fetch_test.php')
        .then(response => response.json())
        .then(data => {
            if (data.test_id) {
                window.location.href = `test.html?test_id=${data.test_id}`;
            } else {
                console.error('Error creating test:', data.error);
            }
        })
        .catch(error => console.error('Error creating test:', error));
}

function loadTest() {
    const testId = new URLSearchParams(window.location.search).get('test_id');
    if (!testId) {
        return;
    }

    fetch(`load_test.php?test_id=${testId}`)
        .then(response => response.json())
        .then(data => {
            const numberOfQuestions = data.questions.length;
            const questionsContainer = document.getElementById('questionsContainer');

            for (let i = 0; i < numberOfQuestions; i++) {
                const questionDiv = document.createElement('div');
                const questionNumber = i + 1;
                questionDiv.className = 'form-group';

                const questionLabel = document.createElement('label');
                questionLabel.innerText = `Question ${questionNumber}: ${data.questions[i].question}`;

                questionDiv.appendChild(questionLabel);

                const options = data.questions[i].answers;
                options.forEach(option => {
                    const optionDiv = document.createElement('div');
                    optionDiv.className = 'form-check';

                    const optionInput = document.createElement('input');
                    optionInput.type = 'radio';
                    optionInput.className = 'form-check-input';
                    optionInput.name = `${data.questions[i].questionId}`;
                    optionInput.id = `${data.questions[i].questionId}_${option.id}`;
                    optionInput.value = option.id;

                    const optionLabel = document.createElement('label');
                    optionLabel.className = 'form-check-label';
                    optionLabel.htmlFor = `${data.questions[i].questionId}_${option.id}`;
                    optionLabel.innerText = option.data;

                    optionDiv.appendChild(optionInput);
                    optionDiv.appendChild(optionLabel);
                    questionDiv.appendChild(optionDiv);
                });

                questionsContainer.prepend(questionDiv);
            }
        })
        .catch(error => console.error('Error fetching data:', error));
}

function submitTest() {
    document.getElementById('questionsContainer').addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(event.target);

        const data = {
            test_id: new URLSearchParams(window.location.search).get('test_id'),
            answers: Array.from(formData.entries()).reduce((obj, [key, value]) => {
                if (!obj[key]) {
                    obj[key] = value;
                } else if (Array.isArray(obj[key])) {
                    obj[key].push(value);
                } else {
                    obj[key] = [obj[key], value];
                }
                return obj;
            }, {})
        };
        console.log(data);

        fetch('submit_test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        }).then(data => {
            console.log('Success:', data);
        })
        //     .catch(error => {
        //     console.error('Error:', error);
        // });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    loadTest();
    submitTest();
});
