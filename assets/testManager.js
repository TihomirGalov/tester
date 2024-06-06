function loadTest() {
    const testId = new URLSearchParams(window.location.search).get('test_id');
    if (!testId) {
        document.getElementById('export-buttons').innerHTML = ''; // Clear existing content
        return;
    }
    document.getElementById('create-test-controls').innerHTML = ''; // Clear existing content

    fetch(`../src/load_test.php?test_id=${testId}`)
        .then(response => response.json())
        .then(data => {
            const numberOfQuestions = data.questions.length;
            const questionsContainer = document.getElementById('questionsContainer');

            for (let i = 0; i < numberOfQuestions; i++) {
                const questionDiv = document.createElement('div');
                questionDiv.className = 'form-group border p-3 mb-3';

                const questionLabel = document.createElement('label');
                questionLabel.innerText = `Question ${i + 1}: ${data.questions[i].question}`;
                questionDiv.appendChild(questionLabel);

                const options = data.questions[i].answers;
                options.forEach((option, index) => {
                    const optionDiv = document.createElement('div');
                    optionDiv.className = 'form-check d-flex align-items-center';

                    const optionLabelLeft = document.createElement('label');
                    optionLabelLeft.className = 'form-check-label mr-2';
                    optionLabelLeft.innerText = `Answer ${index + 1}: `;
                    optionDiv.appendChild(optionLabelLeft);

                    const optionInput = document.createElement('input');
                    optionInput.type = 'radio';
                    optionInput.className = 'form-check-input mr-2';
                    optionInput.name = `${data.questions[i].questionId}`;
                    optionInput.value = option.id;
                    optionDiv.appendChild(optionInput);

                    const optionLabel = document.createElement('label');
                    optionLabel.className = 'form-check-label flex-grow-1';
                    optionLabel.innerText = option.data;
                    optionDiv.appendChild(optionLabel);

                    questionDiv.appendChild(optionDiv);
                });

                questionsContainer.insertBefore(questionDiv, questionsContainer.children[i]);
            }
            document.getElementById('submitBtn').classList.remove('d-none');
        })
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

        fetch('../src/submit_test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(response => {
            if (response.status === 302 || response.redirected) {
                // Manually handle the redirect
                return response.text().then(() => {
                    window.location.href = '../public/results.html';
                });
            } else {
                return response.json();
            }
        }).catch(error => {
            console.error('Error:', error);
        });
    });
}

function addQuestion(question = null, answers = null) {
    const questionsContainer = document.getElementById('questionsContainer');
    const questionsCount = document.getElementsByClassName('form-group border p-3 mb-3').length;
    const questionDiv = document.createElement('div');
    questionDiv.className = 'form-group border p-3 mb-3';

    const questionLabel = document.createElement('label');
    questionLabel.innerText = 'Question:';
    questionDiv.appendChild(questionLabel);

    const questionInput = document.createElement('input');
    questionInput.type = 'text';
    questionInput.className = 'form-control mb-2';
    questionInput.name = 'questions[]';
    questionInput.value = question;
    questionDiv.appendChild(questionInput);

    const answersDiv = document.createElement('div');
    answersDiv.className = 'mb-2';

    for (let i = 0; i < 4; i++) {
        const answerDiv = document.createElement('div');
        answerDiv.className = 'form-check d-flex align-items-center mb-2';

        const correctAnswerInput = document.createElement('input');
        correctAnswerInput.type = 'radio';
        correctAnswerInput.className = 'form-check-input mr-2'; // Set margin-right for spacing
        correctAnswerInput.name = `correct_answers[${questionsCount}]`; // Group radio buttons per question
        correctAnswerInput.value = i;
        correctAnswerInput.checked = answers ? answers[i].is_correct : false;
        answerDiv.appendChild(correctAnswerInput);

        const answerInput = document.createElement('input');
        answerInput.type = 'text';
        answerInput.className = 'form-control';
        answerInput.name = `answers[${questionsCount}][${i}]`; // Use nested array for question and answer
        answerInput.value = answers ? answers[i].value : '';
        answerDiv.appendChild(answerInput);

        answersDiv.appendChild(answerDiv);
    }

    questionDiv.appendChild(answersDiv);
    questionsContainer.appendChild(questionDiv);
    const lastQuestion = document.getElementsByClassName('form-group border p-3 mb-3').length - 1;
    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'btn btn-danger';
    removeButton.innerText = 'Remove Question';
    removeButton.onclick = () => {
        questionsContainer.removeChild(questionDiv);
    };
    questionDiv.appendChild(removeButton);
    //Append question after the lastQuestion
    questionsContainer.insertBefore(questionDiv, questionsContainer.children[lastQuestion]);
}

function createManualTest() {
    const questionsContainer = document.getElementById('questionsContainer');
    questionsContainer.innerHTML = ''; // Clear existing content

    // Add a question when the page loads
    addQuestion();

    // Add "Add Question" button
    const addButton = document.createElement('button');
    addButton.type = 'button';
    addButton.className = 'btn btn-secondary mb-4';
    addButton.innerText = 'Add Question';
    addButton.onclick = addQuestion;

    // Add existing questions button
    const fetchButton = document.createElement('button');
    fetchButton.type = 'button';
    fetchButton.className = 'btn btn-secondary mb-4';
    fetchButton.innerText = 'Add existing questions';
    fetchButton.onclick = fetchQuestions;

    // Add "Save Test" button
    const saveButton = document.createElement('button');
    saveButton.type = 'button';
    saveButton.className = 'btn btn-primary mb-4';
    saveButton.innerText = 'Save Test';
    saveButton.onclick = saveManualTest;

    // Append buttons to the questions container
    questionsContainer.appendChild(addButton);
    questionsContainer.appendChild(fetchButton);
    questionsContainer.appendChild(saveButton);
}

function saveManualTest() {
    const questionsContainer = document.getElementById('questionsContainer');
    const formData = new FormData(questionsContainer);
    let isValid = true;

    const testNameContainer = document.getElementById('testNameContainer');
    const testNameInput = testNameContainer.querySelector('input[name="test_name"]');
    const testName = testNameInput.value;
    if (!testName.trim()) {
        isValid = false;
        alert('Please provide a name for the test.');
    }
    const options = document.getElementById('assignUsers').selectedOptions;
    const users = Array.from(options).map(({value}) => value);

    const data = {
        test_name: testName,
        users: users,
        questions: [],
        answers: [],
        correct_answers: []
    };

    formData.forEach((value, key) => {
        if (key.startsWith('questions')) {
            if (!value.trim()) {
                isValid = false;
                alert('Please fill in all questions.');
            }
            data.questions.push(value);
        } else if (key.startsWith('answers')) {
            const [questionIndex, answerIndex] = key.match(/\d+/g).map(Number);
            if (!value.trim()) {
                isValid = false;
                alert('Please fill in all answers.');
            }
            if (!data.answers[data.questions.length - 1]) {
                data.answers[data.questions.length - 1] = [];
            }
            data.answers[data.questions.length - 1][answerIndex] = value;
        } else if (key.startsWith('correct_answers')) {
            data.correct_answers[data.questions.length - 1] = value;
        }
    });

    if (!isValid) {
        return;
    }

    // Check if at least one radio button is selected for each question
    const questionIndices = Object.keys(data.answers);
    for (const index of questionIndices) {
        const radioButtonName = `correct_answers[${index}]`;
        const radioButtons = questionsContainer.querySelectorAll(`input[name="${radioButtonName}"]`);
        let isOneSelected = false;
        for (const radioButton of radioButtons) {
            if (radioButton.checked) {
                isOneSelected = true;
                break;
            }
        }
        if (!isOneSelected) {
            isValid = false;
            alert(`Please select the correct answer for question ${parseInt(index) + 1}.`);
            break;
        }
    }

    if (!isValid) {
        return;
    }
    //console.error(data)
    fetch('../src/save_manual_test.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    }).then(response => {
        if (response.status === 302 || response.redirected) {
            // Manually handle the redirect
            return response.text().then(() => {
                window.location.href = '../public/index.html';
            });
        } else {
            return response.json();
        }
    })
    // .catch(error => {
    //     console.error('Error:', error);
    // });
    // .catch(error => console.error('Error saving test:', error));
}

function createAndLoadTest() {
    const csvFileInput = document.getElementById('csvFileInput');
    const file = csvFileInput.files[0];

    if (file) {
        const formData = new FormData();
        const options = document.getElementById('assignUsers').selectedOptions;
        const users = Array.from(options).map(({value}) => value);
        formData.append('csvFile', file);
        formData.append('test_name', file.name.replace(/\.[^/.]+$/, "")); // Set test name as file name without extension
        formData.append('users', JSON.stringify(users));
        fetch('../src/fetch_test.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.test_id) {
                    window.location.href = `../public/test.html?test_id=${data.test_id}`;
                } else {
                    console.error('Error creating test:', data.error);
                    alert('Error creating test: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error creating test:', error);
                alert('Error creating test: ' + error.message);
            });
    } else {
        alert('Please select a CSV file to upload.');
    }
}


function showCreateTestOptions() {
    const container = document.querySelector('.container');

    document.getElementById('csvFileInput').classList.add('d-none');
    document.getElementById('testNameContainer').classList.remove('d-none');
    //document.getElementById('assignUsersContainer').classList.remove('d-none');
    document.querySelector('button[onclick="createAndLoadTest()"]').classList.add('d-none');

    // Check if buttons already exist
    if (!container.querySelector('.btn-secondary') || !container.querySelector('.btn-primary')) {
        createManualTest();
    }
}

function loadAllUsers() {
    fetch('../src/load_all_users.php')
        .then(response => response.json())
        .then(data => {
            //fill in the select with the users
            const select = document.getElementById('assignUsers');
            //Delete existing options
            select.innerHTML = '';
            data.users.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.innerText = user.nickname;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading users:', error));

}

document.addEventListener('DOMContentLoaded', function () {
    const createTestButton = document.querySelector('button[onclick="showCreateTestOptions()"]');
    createTestButton.addEventListener('click', createManualTest);
});

document.addEventListener('DOMContentLoaded', function () {
    loadAllUsers();
    loadTest();
    submitTest();
});

function addSelectedQuestions() {
    let selectedQuestions = [];
    document.querySelectorAll('.form-check-input:checked').forEach(checkbox => {
        selectedQuestions.push(checkbox.value);
    });

    // Now you have the selected questions, you can add them to your test
    fetchQuestionsByIds(selectedQuestions);

    $('#questionsModal').modal('hide');
}

function fetchQuestions() {
    fetch('../src/fetch_questions.php')
        .then(response => response.json())
        .then(data => {
            let modalBody = '';
            data.forEach((question, index) => {
                modalBody += `<div class="form-check">
                                <input class="form-check-input" type="checkbox" value="${question.id}" id="question${index}">
                                <label class="form-check-label" for="question${index}">
                                    ${question.description}
                                </label>
                              </div>`;
            });

            let modal = `<div class="modal" tabindex="-1" role="dialog" id="questionsModal">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Select Questions</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        ${modalBody}
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary" id="addQuestions" onclick="addSelectedQuestions()">Add Questions</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>`;

            document.body.insertAdjacentHTML('beforeend', modal);
            $('#questionsModal').modal('show');
        })
        .catch(error => console.error('Error fetching questions:', error));
}

//Create a function that fetches from fetch_questions_by_ids.php and creates questions with answers
function fetchQuestionsByIds(questionIds) {
    fetch(`../src/fetch_questions_by_ids.php?ids=${questionIds}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        },
    }).then(response => response.json())
        .then(data => {
            //Response from the server: [{"id":329,"test_id":17,"description":"\u041a\u0430\u043a\u0432\u043e \u043e\u0437\u043d\u0430\u0447\u0430\u0432\u0430 CSSOM?","answers":[{"value":"Cascading Style Sheets Optimization Method","question_id":329,"is_correct":0,"id":1301},{"value":"CSS Object Model ","question_id":329,"is_correct":1,"id":1302},{"value":"Custom Style Sheets Object Management","question_id":329,"is_correct":0,"id":1303},{"value":"Computed Style Sheets Object Mapping","question_id":329,"is_correct":0,"id":1304}]}]
            data.forEach(question => {
                console.log("HEREQ");
                addQuestion(question.description, question.answers);
            });
        });

}