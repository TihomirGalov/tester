// Function to fetch question details for editing
function fetchQuestionDetails(questionId) {
    fetch(`../src/fetch_question_details.php?id=${questionId}`)
        .then(response => response.json())
        .then(data => {
            const questionForm = document.getElementById('questionForm');
            questionForm.innerHTML = ''; // Clear previous form fields

            const fields = data.fields;
            const creatorId = data.creator_id;
            // Check if the current user is the creator
            let currentUserId = null;

            // Fetch the user ID from the server
            $.ajax({
                url: '../src/get_user_id.php',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    currentUserId = response.user_id;
                    populateForm(fields, creatorId, currentUserId);
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching user ID:', error);
                }
            });

            function populateForm(fields, creatorId, currentUserId) {
                let isEditable = false;
                console.log(fields);
                fields.forEach(field => {
                    const divFormGroup = document.createElement('div');
                    divFormGroup.className = 'form-group mb-3';

                    const label = document.createElement('label');
                    label.innerText = field.label;
                    label.className = 'form-label';
                    divFormGroup.appendChild(label);

                    isEditable = creatorId == currentUserId;

                    if (field.type === 'radio') {
                        const radioContainer = document.createElement('div');
                        radioContainer.className = 'd-flex align-items-center';

                        for (let i = 1; i <= field.options; i++) {
                            const radioDiv = document.createElement('div');
                            radioDiv.className = 'form-check form-check-inline';

                            const input = document.createElement('input');
                            input.type = 'radio';
                            input.className = 'form-check-input';
                            input.name = field.name;
                            input.value = i;
                            if (i === parseInt(field.value)) {
                                input.checked = true;
                            }
                            if (!isEditable) {
                                input.disabled = true;
                            }
                            radioDiv.appendChild(input);

                            const optionLabel = document.createElement('label');
                            optionLabel.className = 'form-check-label';
                            optionLabel.innerText = i;
                            radioDiv.appendChild(optionLabel);

                            radioContainer.appendChild(radioDiv);
                        }
                        divFormGroup.appendChild(radioContainer);

                        if (field.name === 'difficulty_level') {
                            const difficultyExplanation = document.createElement('small');
                            difficultyExplanation.className = 'form-text text-muted';
                            difficultyExplanation.innerText = '(1 много лесен - 5 много труден)';
                            divFormGroup.appendChild(difficultyExplanation);
                        } else if (field.name === 'type') {
                            const difficultyExplanation = document.createElement('small');
                            difficultyExplanation.className = 'form-text text-muted';
                            difficultyExplanation.innerText = '(1 - За предварителни знания; 2 - по време на презентацията; 3 - след презентацията )';
                            divFormGroup.appendChild(difficultyExplanation);
                        }
                        } else if (field.type === 'array') {
                            const reviews = field.value;
                            const reviewsContainer = document.createElement('div');
                            reviewsContainer.className = 'd-flex flex-column';
                            reviews.forEach(review => {
                                const reviewDiv = document.createElement('div');
                                reviewDiv.className = 'border p-2 mb-2';

                                const reviewText = document.createElement('p');
                                reviewText.innerText = review;
                                reviewDiv.appendChild(reviewText);

                                reviewsContainer.appendChild(reviewDiv);
                            });
                            divFormGroup.appendChild(reviewsContainer);
                        } else if (field.name === 'faculty_number') {
                            const input = document.createElement('input');
                            input.type = 'text';
                            input.className = 'form-control';
                            input.name = field.name;
                            input.value = field.value;
                            input.disabled = true;  // Set the input as disabled
                            divFormGroup.appendChild(input);
                        } else {
                            const input = document.createElement('input');
                            input.type = 'text';
                            input.className = 'form-control';
                            input.name = field.name;
                            input.value = field.value;
                            if (!isEditable) {
                                input.disabled = true;
                            }
                            divFormGroup.appendChild(input);
                        }

                        questionForm.appendChild(divFormGroup);
                    });

                if (!isEditable) {
                    document.querySelector('button[type="button"]').disabled = true;
                }
            }
        })
        .catch(error => console.error('Error fetching question details:', error));
}

// Fetch all questions from the database for questions page
function fetchAllQuestions() {
    fetch('../src/fetch_questions.php')
        .then(response => response.json())
        .then(data => {
            const questionList = document.getElementById('questionList');
            questionList.innerHTML = ''; // Clear existing questions

            data.forEach(question => {
                const rating = typeof question.rating === 'number' ? question.rating.toFixed(2) : '0.00';

                const questionButton = document.createElement('button');
                questionButton.type = 'button';
                questionButton.className = 'list-group-item list-group-item-action';
                questionButton.innerText = `${question.test_name}: ${question.description} - ${rating}`;
                questionButton.addEventListener('click', function () {
                    window.location.href = `question_details.html?id=${question.id}`;
                });

                questionList.appendChild(questionButton);
            });
        })
        .catch(error => console.error('Error fetching questions:', error));
}
