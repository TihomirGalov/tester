function loadTest() {
    const testId = new URLSearchParams(window.location.search).get('test_id');
    const edit = new URLSearchParams(window.location.search).get('edit');
    if (!testId) {
        document.getElementById('export-buttons').innerHTML = ''; // Clear existing content
        return;
    } else if (edit === '1') {
        editTest();
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

                    const optionInput = document.createElement('input');
                    optionInput.type = 'radio';
                    optionInput.className = 'form-check-input mr-2';
                    optionInput.name = `${data.questions[i].questionId}`;
                    optionInput.id = `${option.id}_${i}`;
                    optionInput.value = option.id;
                    optionDiv.appendChild(optionInput);

                    const optionLabel = document.createElement('label');
                    optionLabel.className = 'form-check-label flex-grow-1';
                    optionLabel.innerText = `Answer ${index + 1}: ${option.data}`;
                    optionLabel.setAttribute('for', `${option.id}_${i}`); // Set the for attribute to match the input id
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
        const timeTaken = captureTimeTaken();

        formData.delete('time_taken');

        const data = {
            test_id: new URLSearchParams(window.location.search).get('test_id'),
            time_taken: timeTaken,
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

        console.error(data.answers);

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

function addQuestion(question = '', answers = '', purpose = '', type = '', difficultyLevel = 0, feedbackCorrect = '', feedbackIncorrect = '', remarks = '', deleteButton = true) {
    const questionsContainer = document.getElementById('questionsContainer');
    const questionsCount = document.getElementsByClassName('form-group border p-3 mb-3').length;
    const questionDiv = document.createElement('div');
    questionDiv.className = 'form-group border p-3 mb-3';

    // Add question purpose, number, difficulty_level, feedback_correct, feedback_incorrect and remarks
    const questionPurposeLabel = document.createElement('label');
    questionPurposeLabel.innerText = 'Question Purpose:';
    questionDiv.appendChild(questionPurposeLabel);

    const questionPurposeInput = document.createElement('input');
    questionPurposeInput.type = 'text';
    questionPurposeInput.className = 'form-control mb-2';
    questionPurposeInput.name = 'question_purpose[]';
    questionPurposeInput.value = purpose;
    questionDiv.appendChild(questionPurposeInput);
    // Add three dropdown options for question type (1, 2 and 3)
    const questionTypeLabel = document.createElement('label');
    questionTypeLabel.innerText = 'Question Type:';
    questionDiv.appendChild(questionTypeLabel);

    const questionTypeInput = document.createElement('select');
    questionTypeInput.className = 'form-control mb-2';
    questionTypeInput.name = 'question_type[]';


    const option1 = document.createElement('option');
    option1.value = 1;
    option1.text = 'Before presentation';
    questionTypeInput.appendChild(option1);

    const option2 = document.createElement('option');
    option2.value = 2;
    option2.text = 'During presentation';
    questionTypeInput.appendChild(option2);

    const option3 = document.createElement('option');
    option3.value = 3;
    option3.text = 'After presentation';
    questionTypeInput.appendChild(option3)

    questionTypeInput.value = type;
    questionDiv.appendChild(questionTypeInput);


    const difficultyLevelLabel = document.createElement('label');
    difficultyLevelLabel.innerText = 'Difficulty Level:';
    questionDiv.appendChild(difficultyLevelLabel);

    // Set difficulty as a slider with values from 1 to 5
    const difficultyLevelInput = document.createElement('input');
    difficultyLevelInput.type = 'range';
    difficultyLevelInput.className = 'form-control-range mb-2';
    difficultyLevelInput.name = 'difficulty_level[]';
    difficultyLevelInput.min = -5;
    difficultyLevelInput.max = 5;
    difficultyLevelInput.value = difficultyLevel;
    // Add the number value next to the slider
    const difficultyLevelValue = document.createElement('span');
    difficultyLevelValue.innerText = difficultyLevelInput.value;
    questionDiv.appendChild(difficultyLevelValue);
    difficultyLevelInput.oninput = function () {
        difficultyLevelValue.innerText = this.value;
    }
    questionDiv.appendChild(difficultyLevelInput);

    const feedbackCorrectLabel = document.createElement('label');
    feedbackCorrectLabel.innerText = 'Feedback Correct:';
    questionDiv.appendChild(feedbackCorrectLabel);

    const feedbackCorrectInput = document.createElement('input');
    feedbackCorrectInput.type = 'text';
    feedbackCorrectInput.className = 'form-control mb-2';
    feedbackCorrectInput.name = 'feedback_correct[]';
    feedbackCorrectInput.value = feedbackCorrect;
    questionDiv.appendChild(feedbackCorrectInput);

    const feedbackIncorrectLabel = document.createElement('label');
    feedbackIncorrectLabel.innerText = 'Feedback Incorrect:';
    questionDiv.appendChild(feedbackIncorrectLabel);

    const feedbackIncorrectInput = document.createElement('input');
    feedbackIncorrectInput.type = 'text';
    feedbackIncorrectInput.className = 'form-control mb-2';
    feedbackIncorrectInput.name = 'feedback_incorrect[]';
    feedbackIncorrectInput.value = feedbackIncorrect;
    questionDiv.appendChild(feedbackIncorrectInput);

    const remarksLabel = document.createElement('label');
    remarksLabel.innerText = 'Remarks:';
    questionDiv.appendChild(remarksLabel);

    const remarksInput = document.createElement('input');
    remarksInput.type = 'text';
    remarksInput.className = 'form-control mb-2';
    remarksInput.name = 'remarks[]';
    remarksInput.value = remarks;
    questionDiv.appendChild(remarksInput);

    const questionLabel = document.createElement('label');
    questionLabel.innerText = 'Question:';
    questionDiv.appendChild(questionLabel);

    const questionInput = document.createElement('input');
    questionInput.type = 'text';
    questionInput.className = 'form-control mb-2';
    questionInput.name = 'questions[]';
    if (typeof question === 'string') {
        questionInput.value = question;
    } else {
        questionInput.value = '';
    }
    questionDiv.appendChild(questionInput);

    const answersDiv = document.createElement('div');
    answersDiv.className = 'mb-2';
    answersDiv.id = `answers${questionsCount}`;

    for (let i = 0; i < 4; i++) {
        const answerDiv = document.createElement('div');
        answerDiv.className = 'form-check d-flex align-items-center mb-2';

        const correctAnswerInput = document.createElement('input');
        correctAnswerInput.type = 'radio';
        correctAnswerInput.className = 'form-check-input mr-2'; // Set margin-right for spacing
        correctAnswerInput.name = `correct_answers[${questionsCount}]`; // Group radio buttons per question
        correctAnswerInput.id = `correctAnswer${i}_${questionsCount}`;
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
    removeButton.className = 'btn btn-danger delete-button';
    removeButton.innerText = 'Remove Question';
    removeButton.onclick = () => {
        questionDiv.remove();
    };

    if (!deleteButton) {
        removeButton.style.display = 'none';
    }
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

    // Create buttons flex container
    const buttonsContainer = document.createElement('div');
    buttonsContainer.className = 'd-flex justify-content-between';
    // Set width to the container
    buttonsContainer.style.width = '40%';
    buttonsContainer.appendChild(saveButton);
    buttonsContainer.appendChild(addButton);
    buttonsContainer.appendChild(fetchButton);

    // Append buttons to the questions container
    questionsContainer.appendChild(buttonsContainer);
}

function getTestData() {
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

    const assignAllCheckbox = document.getElementById('assignAllUsers');
    let users = [];
    if (assignAllCheckbox.checked) {
        // If "Assign All Users" is checked, include all user IDs
        const allOptions = document.getElementById('assignUsers').options;
        for (let option of allOptions) {
            users.push(option.value);
        }
    } else {
        // Otherwise, include only selected user IDs
        const selectedOptions = document.getElementById('assignUsers').selectedOptions;
        users = Array.from(selectedOptions).map(({value}) => value);
    }

    const data = {
        test_name: testName,
        users: users,
        questions: [],
        answers: [],
        correct_answers: [],
        question_purposes: [],
        question_types: [],
        difficulty_levels: [],
        feedbacks_correct: [],
        feedbacks_incorrect: [],
        remarks: []
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
        } else if (key.startsWith('question_purpose')) {
            data.question_purposes.push(value);
        } else if (key.startsWith('question_type')) {
            data.question_types.push(value);
        } else if (key.startsWith('difficulty_level')) {
            data.difficulty_levels.push(value);
        } else if (key.startsWith('feedback_correct')) {
            data.feedbacks_correct.push(value);
        } else if (key.startsWith('feedback_incorrect')) {
            data.feedbacks_incorrect.push(value);
        } else if (key.startsWith('remarks')) {
            data.remarks.push(value);
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

    return data;
}

function saveManualTest() {
    const data = getTestData();

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
        } else if (response.status === 403) {
            alert("You do not have permission to create a test!");
        }else {
            return response.json();
        }
    }).catch(error => {
        console.error('Error:', error);
    });
}


function showCreateTestOptions() {
    const container = document.querySelector('.container');


    document.getElementById('csvFileInput').classList.add('d-none');
    document.getElementById('importCsvBtn').style.display = 'none';
    document.getElementById('createTest').style.display = 'none'; // Hide the "Create Test" button
    document.getElementById('testNameContainer').classList.remove('d-none');

    // Check if buttons already exist
    if (!container.querySelector('.btn-secondary') || !container.querySelector('.btn-primary')) {
        createManualTest();
    }
}

function toggleAssignAll() {
    const assignAllCheckbox = document.getElementById('assignAllUsers');
    const assignUsersSelect = document.getElementById('assignUsers');

    if (assignAllCheckbox.checked) {
        // Disable the multi-select and clear selected options
        assignUsersSelect.disabled = true;
        for (let option of assignUsersSelect.options) {
            option.selected = true;
        }
    } else {
        // Enable the multi-select
        assignUsersSelect.disabled = false;
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
function fetchQuestionsByIds(questionIds, deleteButton = true) {
    fetch(`../src/fetch_questions_by_ids.php?ids=${questionIds}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        },
    }).then(response => response.json())
        .then(data => {
            //Response from the server: [{"id":329,"test_id":17,"description":"\u041a\u0430\u043a\u0432\u043e \u043e\u0437\u043d\u0430\u0447\u0430\u0432\u0430 CSSOM?","answers":[{"value":"Cascading Style Sheets Optimization Method","question_id":329,"is_correct":0,"id":1301},{"value":"CSS Object Model ","question_id":329,"is_correct":1,"id":1302},{"value":"Custom Style Sheets Object Management","question_id":329,"is_correct":0,"id":1303},{"value":"Computed Style Sheets Object Mapping","question_id":329,"is_correct":0,"id":1304}]}]
            data.forEach(question => {
                addQuestion(
                    question.description,
                    question.answers,
                    question.purpose,
                    question.type,
                    question.difficulty_level,
                    question.feedback_correct,
                    question.feedback_incorrect,
                    question.remarks,
                    deleteButton
                )
            });
        });
}

let lines = []; // Define lines outside the function
let totalCount = 0; // Define total count of questions

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const text = e.target.result;
            lines = text.split('\n').slice(1).map(line => line.split(','));
            const creators = [...new Set(lines.map(line => line[1]).filter(creator => creator))];
            populateDropdown('creator', creators);
            document.getElementById('importCsvBtn').style.display = 'block';
        };
        reader.readAsText(file);
    }
}

function populateDropdown(dropdownId, values) {
    const dropdown = document.getElementById(dropdownId);
    dropdown.innerHTML = '';

    // Add a blank option
    const blankOption = document.createElement('option');
    blankOption.text = '';
    blankOption.value = '';
    dropdown.add(blankOption);
    values.forEach(value => {
        const option = document.createElement('option');
        // Remove leading and trailing quotation marks
        option.text = value.replace(/^"(.*)"$/, '$1');
        option.value = value;
        dropdown.add(option);
    });
    // Listen for changes in the creator dropdown
    if (dropdownId === 'creator') {
        dropdown.addEventListener('change', function () {
            const selectedCreator = this.value;
            // Filter purposes based on the selected creator
            const filteredLines = lines
                .filter(line => line[1] === selectedCreator) // Filter rows by selected creator
                .map(line => line[3]);
            const filteredPurposes = filteredLines// Extract purposes from filtered rows
                .filter((purpose, index, self) => self.indexOf(purpose) === index); // Remove duplicates
            const totalCount = filteredLines.length;
            // Populate the testPurpose dropdown with filtered purposes
            populateDropdown('testPurpose', filteredPurposes, selectedCreator);
            // Update the total count of questions
            updateTotalCount(totalCount);
        });
    }
    // Listen for changes in the testPurpose dropdown
    if (dropdownId === 'testPurpose') {
        dropdown.addEventListener('change', function () {
            const selectedCreator = document.getElementById('creator').value;
            const selectedPurpose = this.value;
            // Filter lines based on the selected creator and purpose
            const filteredLines = lines.filter(line => line[1] === selectedCreator && line[3] === selectedPurpose);
            // Count the number of questions for the selected creator and purpose
            const totalCount = filteredLines.length;
            updateTotalCount(totalCount);
        });
    }
}

function updateTotalCount(count) {
    const totalCountContainer = document.getElementById('totalCountContainer');
    if (count !== undefined) {
        totalCountContainer.textContent = `Total Count of Questions: ${count}`;
        totalCount = parseInt(count);
    } else {
        totalCountContainer.textContent = '';
        totalCount = 0;
    }
}

function handleImport() {
    const creator = document.getElementById('creator').value.replace(/^"(.*)"$/, '$1');
    const testPurpose = document.getElementById('testPurpose').value.replace(/^"(.*)"$/, '$1');
    const range = document.getElementById('questionRange').value;
    const [rangeStart, rangeEnd] = range.split('-').map(Number);
    const errorContainer = document.getElementById('errorContainer');

    // Check if creator is selected
    if (creator === '') {
        errorContainer.style.display = 'block';
        errorContainer.textContent = 'Please select a creator.';
        return;
    } else {
        // Hide error message if the creator is valid
        errorContainer.style.display = 'none';
        errorContainer.textContent = '';
    }

    // Check if test purpose is selected
    if (testPurpose === '') {
        errorContainer.style.display = 'block';
        errorContainer.textContent = 'Please select a test purpose.';
        return;
    } else {
        // Hide error message if the test purpose is valid
        errorContainer.style.display = 'none';
        errorContainer.textContent = '';
    }

    // Check if the range is valid
    if (isNaN(rangeStart) || isNaN(rangeEnd) || rangeStart < 1 || rangeEnd < rangeStart || rangeEnd > totalCount) {
        // Show error message
        errorContainer.style.display = 'block';
        errorContainer.textContent = 'Invalid range. Please enter a valid range between 1 and ' + totalCount + '.';
        return;
    } else {
        // Hide error message if the range is valid
        errorContainer.style.display = 'none';
        errorContainer.textContent = '';
    }

    const questionRange = document.getElementById('questionRange').value;

    const csvFileInput = document.getElementById('csvFileInput');
    const file = csvFileInput.files[0];

    if (!file) {
        errorContainer.style.display = 'block';
        errorContainer.textContent = 'Please select a CSV file to upload.';
        return;
    }

    const formData = new FormData();
    const options = document.getElementById('assignUsers').selectedOptions;
    const users = Array.from(options).map(({value}) => value);

    formData.append('csvFile', file);
    formData.append('test_name', file.name.replace(/\.[^/.]+$/, "")); // Set test name as file name without extension
    formData.append('users', JSON.stringify(users));
    formData.append('question_range', questionRange);
    formData.append('creator', creator);
    formData.append('test_purpose', testPurpose);

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
                errorContainer.style.display = 'block';
                errorContainer.textContent = 'Error creating test: ' + data.error;
            }
        })
        .catch(error => {
            console.error('Error creating test:', error);
            errorContainer.style.display = 'block';
            errorContainer.textContent = 'Error creating test: ' + error.message;
        });
}

// Global variables for timer
let startTime = null;
let timerInterval = null;

// Function to start the timer
function startTimer() {
    startTime = new Date();
    // Update time display every second
    timerInterval = setInterval(updateTimeDisplay, 1000);
}

// Function to capture time taken in seconds
function captureTimeTaken() {
    if (!startTime) {
        return 0;
    }
    const endTime = new Date();
    return Math.floor((endTime - startTime) / 1000); // Time in seconds
}

// Function to update time display
function updateTimeDisplay() {
    const timeTaken = captureTimeTaken();
    const timeDisplay = document.getElementById('timeElapsed');
    if (timeDisplay) {
        timeDisplay.textContent = formatTime(timeTaken);
    }
}

// Function to format time in MM:SS format
function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
}

// Function to show/hide time display based on URL parameter
function toggleTimeDisplay() {
    const urlParams = new URLSearchParams(window.location.search);
    const testId = urlParams.get('test_id');
    const edit = urlParams.get('edit');
    const timeDisplay = document.getElementById('timer');
    if (edit !== '1' && testId && timeDisplay) {
        timeDisplay.style.display = 'block';
    } else {
        timeDisplay.style.display = 'none';
    }
}

function deleteTest() {
    const testId = new URLSearchParams(window.location.search).get('test_id');
    fetch(`../src/delete_test.php?test_id=${testId}`, {
        method: 'DELETE'
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

function updateTest(questionIds, answerIds) {
    const data = getTestData();

    if (!data) {
        return;
    }

    data.test_id = new URLSearchParams(window.location.search).get('test_id');
    data.question_ids = questionIds;
    data.answer_ids = answerIds;

    fetch('../src/update_test.php', {
        method: 'PUT',
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
    }).catch(error => {
        console.error('Error:', error);
    });
}

function editTest() {
    const edit = new URLSearchParams(window.location.search).get('edit');
    const testId = new URLSearchParams(window.location.search).get('test_id');
    if (edit !== '1' || !testId) {
        return;
    }

    document.getElementById('export-buttons').innerHTML = ''; // Clear existing content
    document.getElementById('createTest').style.display = 'none'; // Hide the "Create Test" button
    document.getElementById('csvFileInput').style.display = 'none'; // Hide the CSV file input
    document.getElementById('importCsvBtn').style.display = 'none'; // Hide the CSV file input
    document.getElementById('assignUsersContainer').style.display = 'none'; // Hide the assign users container
    document.getElementById('testNameContainer').classList.remove('d-none');

    var questionIds = [];
    var answerIds = [];

    fetch(`../src/load_test.php?test_id=${testId}`).then(response => response.json()).then(data => {
        //Get question ids and execute fetchQuestionsByIds
        questionIds = data.questions.map(question => question.questionId);
        answerIds = data.questions.map(question => question.answers.map(answer => answer.id)).flat();
        document.getElementById('testName').value = data.testName;

        fetchQuestionsByIds(questionIds, false);
    }).then(() => {

        // Add "Save Test" button and Delete Test button
        const saveButton = document.createElement('button');
        saveButton.type = 'button';
        saveButton.className = 'btn btn-primary mb-4';
        saveButton.innerText = 'Save Test';
        saveButton.onclick = () => {
            updateTest(questionIds, answerIds);
        };

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'btn btn-danger mb-4';
        deleteButton.innerText = 'Delete Test';
        deleteButton.onclick = deleteTest;

        // Create Buttons flex container
        const buttonsContainer = document.createElement('div');
        buttonsContainer.className = 'd-flex justify-content-between';
        buttonsContainer.appendChild(saveButton);
        buttonsContainer.appendChild(deleteButton);

        // Append buttons to the questions container
        document.getElementById('questionsContainer').appendChild(buttonsContainer);
    });
}

// Add event listener for form submission to include time taken
document.getElementById('questionsContainer').addEventListener('submit', function (event) {
    const timeTaken = captureTimeTaken();
    const timeTakenInput = document.createElement('input');
    timeTakenInput.type = 'hidden';
    timeTakenInput.name = 'time_taken';
    timeTakenInput.value = timeTaken;
    event.target.appendChild(timeTakenInput);
    clearInterval(timerInterval); // Stop the timer
});

// Add event listener for page load
document.addEventListener('DOMContentLoaded', function () {
    startTimer();
    toggleTimeDisplay(); // Initially toggle time display based on URL parameter
});


document.addEventListener('DOMContentLoaded', function () {
    loadAllUsers();
    loadTest();
    submitTest();
    document.getElementById('csvFileInput').addEventListener('change', handleFileSelect);
});