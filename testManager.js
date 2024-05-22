function loadTest() {
    const testId = new URLSearchParams(window.location.search).get('test_id');
    fetch(`load_test.php?test_id=${testId}`)
        .then(response => response.json())
        .then(data => {
            const numberOfQuestions = data.questions.length;
            const questionsContainer = document.getElementById('questionsContainer');

            for (let i = 0; i < numberOfQuestions; i++) {
                const questionDiv = document.createElement('div');
                const questionNumber = numberOfQuestions - i;
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
                    optionInput.id = data.questions[i].questionId;
                    optionInput.value = option.id;

                    const optionLabel = document.createElement('label');
                    optionLabel.className = 'form-check-label';
                    optionLabel.htmlFor = 'question' + questionNumber;
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

        const data = Array.from(formData.entries()).reduce((obj, [key, value]) => {
            if (!obj.answers) {
                obj.answers = {[key]: value};
            } else {
                obj.answers[key] = value;
            }
            return obj;
        }, {});
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
            // handle response data
        }).catch(error => {
            console.error('Error:', error);
        });
    });
}