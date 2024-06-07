// Function to fetch question details for editing
function fetchQuestionDetails(questionId) {
    fetch(`../src/fetch_question_details.php?id=${questionId}`)
        .then(response => response.json())
        .then(data => {
            console.error(data);

            const questionForm = document.getElementById('questionForm');
            questionForm.innerHTML = ''; // Clear previous form fields

            data.forEach(field => {
                const divFormGroup = document.createElement('div');
                divFormGroup.className = 'form-group';

                const label = document.createElement('label');
                label.innerText = field.label;
                divFormGroup.appendChild(label);

                if (field.type === 'radio') {
                    for (let i = 1; i <= field.options; i++) {
                        const input = document.createElement('input');
                        input.type = 'radio';
                        input.className = 'form-check-input';
                        input.name = field.name;
                        input.value = i;
                        if (i === parseInt(field.value)) {
                            input.checked = true;
                        }
                        divFormGroup.appendChild(input);

                        const optionLabel = document.createElement('label');
                        optionLabel.className = 'form-check-label';
                        optionLabel.innerText = i;
                        divFormGroup.appendChild(optionLabel);
                    }
                } else {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'form-control';
                    input.name = field.name;
                    input.value = field.value;
                    divFormGroup.appendChild(input);
                }

                questionForm.appendChild(divFormGroup);
            });
        })
        .catch(error => console.error('Error fetching question details:', error));
}

// Fetch all questions from the database for questions page
function fetchAllQuestions() {
    fetch('../src/fetch_questions.php')
        .then(response => response.json())
        .then(data => {
            const questionList = document.getElementById('questionList');
            data.forEach(question => {
                const questionButton = document.createElement('button');
                questionButton.type = 'button';
                questionButton.className = 'list-group-item list-group-item-action';
                questionButton.innerText = question.description;
                questionButton.addEventListener('click', function () {
                    window.location.href = `question_details.html?id=${question.id}`;
                });
                questionList.appendChild(questionButton);
            });
        })
        .catch(error => console.error('Error fetching questions:', error));
}