function loadTest() {
    const testId = new URLSearchParams(window.location.search).get('test_id');
    if (!testId) {
        return;
    }

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

                questionsContainer.appendChild(questionDiv);
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

function createManualTest() {
    loadAllUsers();
    const questionsContainer = document.getElementById('questionsContainer');
    questionsContainer.innerHTML = ''; // Clear existing content

    const addQuestion = () => {
        const questionDiv = document.createElement('div');
        questionDiv.className = 'form-group border p-3 mb-3';

        const questionLabel = document.createElement('label');
        questionLabel.innerText = 'Question:';
        questionDiv.appendChild(questionLabel);

        const questionInput = document.createElement('input');
        questionInput.type = 'text';
        questionInput.className = 'form-control mb-2';
        questionInput.name = 'questions[]';
        questionDiv.appendChild(questionInput);

        const answersDiv = document.createElement('div');
        answersDiv.className = 'mb-2';

        for (let i = 0; i < 4; i++) {
            const answerDiv = document.createElement('div');
            answerDiv.className = 'form-check d-flex align-items-center mb-2';

            const correctAnswerInput = document.createElement('input');
            correctAnswerInput.type = 'radio';
            correctAnswerInput.className = 'form-check-input mr-2'; // Set margin-right for spacing
            correctAnswerInput.name = `correct_answers[${questionsContainer.children.length}]`; // Group radio buttons per question
            correctAnswerInput.value = i;
            answerDiv.appendChild(correctAnswerInput);

            const answerInput = document.createElement('input');
            answerInput.type = 'text';
            answerInput.className = 'form-control';
            answerInput.name = `answers[${questionsContainer.children.length}][${i}]`; // Use nested array for question and answer
            answerDiv.appendChild(answerInput);

            answersDiv.appendChild(answerDiv);
        }

        questionDiv.appendChild(answersDiv);
        questionsContainer.appendChild(questionDiv);
    };

    // Add a question when the page loads
    addQuestion();

    // Add "Add Question" button
    const addButton = document.createElement('button');
    addButton.type = 'button';
    addButton.className = 'btn btn-secondary mb-4';
    addButton.innerText = 'Add Question';
    addButton.onclick = addQuestion;

    // Add "Save Test" button
    const saveButton = document.createElement('button');
    saveButton.type = 'button';
    saveButton.className = 'btn btn-primary mb-4';
    saveButton.innerText = 'Save Test';
    saveButton.onclick = saveManualTest;

    // Append buttons to the questions container
    questionsContainer.appendChild(addButton);
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
    const users = Array.from(options).map(({ value }) => value);

    const data = {
        test_name: testName,
        users: users,
        questions: [],
        answers: {},
        correct_answers: {}
    };

    formData.forEach((value, key) => {
        if (key.startsWith('questions')) {
            if (!value.trim()) {
                isValid = false;
                alert('Please fill in all questions.');
            }
            // console.log("question: ", value)
            data.questions.push(value);
        } else if (key.startsWith('answers')) {
            const [questionIndex, answerIndex] = key.match(/\d+/g).map(Number);
            if (!value.trim()) {
                isValid = false;
                alert('Please fill in all answers.');
            }
            if (!data.answers[questionIndex]) {
                data.answers[questionIndex] = [];
            }
            data.answers[questionIndex][answerIndex] = value;
        } else if (key.startsWith('correct_answers')) {
            const questionIndex = key.match(/\d+/)[0];
            data.correct_answers[questionIndex] = value;
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
    loadAllUsers();
    const csvFileInput = document.getElementById('csvFileInput');
    const file = csvFileInput.files[0];
    if (file) {
        const formData = new FormData();
        const options = document.getElementById('assignUsers').selectedOptions;
        const users = Array.from(options).map(({ value }) => value);
        formData.append('csvFile', file);
        formData.append('test_name', file.name.replace(/\.[^/.]+$/, "")); // Set test name as file name without extension
        formData.append('users', JSON.stringify(users));

        fetch('../src/fetch_test.php', {
            method: 'POST',
            body: formData
        }).then(response => {
            if (response.status === 302 || response.redirected) {
                // Manually handle the redirect
                return response.text().then(() => {
                    window.location.href = '../public/index.html';
                });
            } else {
                return response.json();
            }
        }).catch(error => {
            console.error('Error:', error);
        });
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

document.addEventListener('DOMContentLoaded', function() {
    const createTestButton = document.querySelector('button[onclick="showCreateTestOptions()"]');
    createTestButton.addEventListener('click', createManualTest);
});

document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.getElementById('exportBtn');
    exportBtn.addEventListener('click', function() {
        const testId = new URLSearchParams(window.location.search).get('test_id');
        if (testId) {
            window.location.href = `../src/export_test.php?test_id=${testId}`;
        } else {
            alert('Test ID not found');
        }
    });

    loadTest();
    submitTest();
});